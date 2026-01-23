<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use App\Models\DataClient;
use App\Models\ServiceMasuk;
use App\Models\Category;

class Pelayanan extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Pelayanan';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $title = 'Pelayanan Service';

    protected static string $view = 'filament.pages.pelayanan';

    /**
     * State form
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'tanggal_masuk' => now(),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([

                /* =====================
                 * DATA CLIENT
                 * ===================== */
                Forms\Components\Section::make('Data Client')
                    ->description('Informasi pelanggan')
                    ->schema([
                        Forms\Components\TextInput::make('nama')
                            ->label('Nama Client')
                            ->required()
                            ->maxLength(100),

                        Forms\Components\TextInput::make('nomor_wa')
                            ->label('Nomor WhatsApp')
                            ->required()
                            ->tel()
                            ->helperText('Gunakan format 628xxxx'),
                    ])
                    ->columns(2),

                /* =====================
                 * DATA SERVICE
                 * ===================== */
                Forms\Components\Section::make('Data Service Masuk')
                    ->description('Detail perangkat & kerusakan')
                    ->schema([
                        Forms\Components\Select::make('category_id')
                            ->label('Kategori')
                            ->options(Category::query()->pluck('category', 'id'))
                            ->searchable()
                            ->required(),

                        Forms\Components\DatePicker::make('tanggal_masuk')
                            ->label('Tanggal Masuk')
                            ->required(),

                        Forms\Components\Textarea::make('kerusakan')
                            ->label('Kerusakan')
                            ->required()
                            ->columnSpanFull(),

                        Forms\Components\CheckboxList::make('perlengkapan')
                            ->label('Perlengkapan')
                            ->options([
                                'tas' => 'Tas',
                                'adaptor_charger' => 'Adaptor Charger',
                                'kabel_power' => 'Kabel Power',
                                'kabel_usb_print' => 'Kabel USB Print',
                                'kardus' => 'Kardus',
                                'battrai' => 'Battrai',
                                'kesing_kanan' => 'Kesing Kanan',
                                'kesing_kiri' => 'Kesing Kiri',
                                'usb_data' => 'USB Data',
                            ])
                            ->columns(3),

                        Forms\Components\Textarea::make('keterangan')
                            ->label('Keterangan Tambahan')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * Submit form
     */
    public function submit(): void
    {
        $this->form->validate();

        $service = null;

        DB::transaction(function () use (&$service) {

            $client = DataClient::create([
                'nama' => $this->data['nama'],
                'nomor_wa' => $this->data['nomor_wa'],
            ]);

            $service = ServiceMasuk::create([
                'category_id' => $this->data['category_id'],
                'nama_client' => $client->nama,
                'nomor_wa' => $client->nomor_wa,
                'tanggal_masuk' => $this->data['tanggal_masuk'],
                'kerusakan' => $this->data['kerusakan'],
                'perlengkapan' => $this->data['perlengkapan'] ?? [],
                'keterangan' => $this->data['keterangan'] ?? null,
            ]);
        });

        // 🔥 WAJIB load relasi
        $service->load('category');

        // URL WhatsApp
        $waUrl = $this->sendWhatsapp($service);

        Notification::make()
            ->title('Berhasil')
            ->body('Data pelayanan berhasil disimpan')
            ->success()
            ->send();

        $this->reset('data');

        // 🔥 Buka WA + Print
        $this->js("
        window.open('{$waUrl}', '_blank');
        window.location.href = '" . route('service.print', $service->id) . "';
    ");
    }


    private function sendWhatsapp(ServiceMasuk $service): string
    {
        $text = urlencode(
            "Halo {$service->nama_client},\n\n" .
                "Service Anda sudah kami terima.\n\n" .
                "Kategori: {$service->category->category}\n" .
                "Kerusakan: {$service->kerusakan}\n" .
                "Tanggal: {$service->tanggal_masuk->format('d-m-Y')}\n\n" .
                "Terima kasih 🙏"
        );

        return "https://wa.me/{$service->nomor_wa}?text={$text}";
    }
}
