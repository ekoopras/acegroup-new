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
use App\Models\Kerusakan;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Components\Group;

class Pelayanan extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationGroup = 'Pelayanan';
    protected static ?string $navigationLabel = 'Pelayanan';
    protected static ?string $title = 'Input Pelayanan Service';

    public ?ServiceMasuk $servicePreview = null;
    public array $serviceIds = [];
    public ?string $waUrl = null; // Properti untuk menampung link WA

    protected static string $view = 'filament.pages.pelayanan';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'services' => [
                [
                    'tanggal_masuk' => now(),
                ],
            ],
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->statePath('data')
            ->schema([
                Section::make('Informasi Client')
                    ->schema([
                        Select::make('search_client')
                            ->label('Cari Client')
                            ->placeholder('Ketik nama/WA...')
                            ->searchable()
                            ->preload()
                            ->options(DataClient::all()->mapWithKeys(function ($client) {
                                return [$client->id => "{$client->nama} - {$client->nomor_wa}"];
                            }))
                            ->live()
                            ->afterStateUpdated(function ($state, Set $set) {
                                if ($state) {
                                    $client = DataClient::find($state);
                                    if ($client) {
                                        $set('nama', $client->nama);
                                        $set('nomor_wa', $client->nomor_wa);
                                    }
                                }
                            })
                            ->columnSpanFull(),

                        TextInput::make('nama')
                            ->label('Nama Client')
                            ->required()
                            ->extraInputAttributes(['style' => 'text-transform: capitalize;']),

                        TextInput::make('nomor_wa')
                            ->label('WhatsApp')
                            ->tel()
                            ->required(),
                    ])->columns(2),

                Repeater::make('services')
                    ->label('Daftar Barang Service')
                    ->defaultItems(1)
                    ->addActionLabel('+ Tambah Barang')
                    ->collapsible()
                    ->cloneable()
                    ->schema([
                        Grid::make(['default' => 1, 'lg' => 3])->schema([
                            Group::make([
                                Grid::make(2)->schema([
                                    Grid::make(3)->schema([
                                        Select::make('category_id')
                                            ->label('Kategori')
                                            ->options(Category::pluck('category', 'id'))
                                            ->searchable()
                                            ->live()
                                            ->required(),

                                        TextInput::make('nama_barang')
                                            ->label('Nama Barang')
                                            ->required()
                                            ->extraInputAttributes(['style' => 'text-transform: capitalize;']),

                                        DatePicker::make('tanggal_masuk')
                                            ->label('Tgl Masuk')
                                            ->default(now())
                                            ->required(),
                                    ])->columnSpanFull(),

                                    Select::make('kerusakan')
                                        ->label('Kerusakan')
                                        ->multiple()
                                        ->searchable()
                                        ->options(
                                            fn(Get $get) =>
                                            Kerusakan::where('category_id', $get('category_id'))->pluck('nama_kerusakan', 'nama_kerusakan')
                                        )
                                        ->createOptionForm([TextInput::make('nama_kerusakan')->required()])
                                        ->createOptionUsing(function (array $data, Get $get) {
                                            return Kerusakan::create([
                                                'category_id' => $get('category_id'),
                                                'nama_kerusakan' => $data['nama_kerusakan'],
                                            ])->nama_kerusakan;
                                        })
                                        ->required()
                                        ->columnSpanFull(),

                                    Textarea::make('keterangan')
                                        ->label('Keterangan Tambahan')
                                        ->rows(3)
                                        ->columnSpanFull(),

                                    Hidden::make('token')
                                        ->default(fn() => str()->random(32)),
                                ])
                            ])->columnSpan(['lg' => 2]),

                            Section::make('Perlengkapan')
                                ->compact()
                                ->columnSpan(['lg' => 1])
                                ->schema([
                                    CheckboxList::make('perlengkapan')
                                        ->label('')
                                        ->options([
                                            'unit_only' => 'Unit Only',
                                            'tas' => 'Tas',
                                            'adaptor_charger' => 'Adaptor',
                                            'kabel_power' => 'Kabel Power',
                                            'kabel_usb_print' => 'USB Print',
                                            'kardus' => 'Kardus',
                                            'battrai' => 'Baterai',
                                            'kesing_kanan' => 'Kesing R',
                                            'kesing_kiri' => 'Kesing L',
                                            'usb_data' => 'USB Data',
                                        ])
                                        ->required(),
                                ]),
                        ]),
                    ])->columnSpanFull(),
            ]);
    }

    public function submit(): void
    {
        $this->form->validate();

        $this->servicePreview = null;
        $this->serviceIds = [];
        $currentClient = null;

        DB::transaction(function () use (&$currentClient) {
            $currentClient = DataClient::firstOrCreate(
                ['nomor_wa' => $this->data['nomor_wa']],
                ['nama' => $this->data['nama']]
            );

            foreach ($this->data['services'] as $service) {
                $createdService = ServiceMasuk::create([
                    'category_id'   => $service['category_id'],
                    'nama_barang'   => $service['nama_barang'],
                    'data_client_id' => $currentClient->id,
                    'tanggal_masuk' => $service['tanggal_masuk'],
                    'kerusakan'     => $service['kerusakan'] ?? null,
                    'perlengkapan'  => $service['perlengkapan'] ?? [],
                    'keterangan'    => $service['keterangan'] ?? null,
                    'token'         => $service['token'],
                ]);

                $this->serviceIds[] = $createdService->id;

                if ($this->servicePreview === null) {
                    $this->servicePreview = $createdService;
                }
            }
        });

        // Ambil data terbaru untuk WhatsApp
        $allServices = ServiceMasuk::with('category')->whereIn('id', $this->serviceIds)->get();

        // Simpan URL WhatsApp ke properti agar bisa diakses Action modal
        $this->waUrl = $this->sendWhatsapp($currentClient, $allServices);

        Notification::make()
            ->title('Berhasil')
            ->body('Data berhasil disimpan')
            ->success()
            ->send();

        $this->mountAction('preview');

        // Reset form
        $this->reset('data');
        $this->form->fill(['services' => [['tanggal_masuk' => now()]]]);
    }

    protected function getActions(): array
    {
        return [
            Action::make('preview')
                ->modalHeading('Data Service Berhasil Disimpan')
                ->modalWidth('lg')
                ->modalSubmitAction(false)
                ->modalCancelAction(false)
                ->modalActions([
                    Action::make('print')
                        ->label('🖨 Print Semua')
                        ->action(function () {
                            foreach ($this->serviceIds as $id) {
                                $this->js("window.open('" . route('service.print', $id) . "', '_blank');");
                            }
                        }),

                    Action::make('wa')
                        ->label('📲 Kirim WhatsApp')
                        ->color('success')
                        ->url(fn() => $this->waUrl) // Menggunakan URL yang sudah digenerate di submit()
                        ->openUrlInNewTab(),
                ])
                ->modalContent(fn() => view('filament.service.preview', [
                    'service' => $this->servicePreview,
                ])),
        ];
    }

    private function sendWhatsapp($client, $services): string
    {
        $items = $services ?? [];

        $pesan = "Asallamuallaikum *{$client->nama}*\n\n";
        $pesan .= "Service anda sudah kami terima. Berikut daftar unit anda:\n\n";

        foreach ($items as $index => $item) {
            $categoryName = $item->category->category ?? 'Unit';
            $kerusakanText = is_array($item->kerusakan) ? implode(', ', $item->kerusakan) : ($item->kerusakan ?? '-');
            $perlengkapanText = is_array($item->perlengkapan) ? implode(', ', $item->perlengkapan) : ($item->perlengkapan ?? '-');
            $linkTracking = url("/tracking/{$item->token}");

            $no = $index + 1;
            $pesan .= "*No. {$no}*\n";
            $pesan .= "Unit: {$categoryName} {$item->nama_barang}\n";
            $pesan .= "Trouble: {$kerusakanText}\n";
            $pesan .= "Kelengkapan: {$perlengkapanText}\n";
            $pesan .= "Tracking: {$linkTracking}\n";
            $pesan .= "----------------------------\n";
        }

        $pesan .= "\nUntuk pengambilan unit akan kami infokan kembali dengan QR Code pengambilan.\n\n";
        $pesan .= "Hormat kami,\n*Acegroup Service Center*";

        $nomor_wa = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $client->nomor_wa));

        return "https://api.whatsapp.com/send?phone={$nomor_wa}&text=" . urlencode($pesan);
    }

    // Menolak akses ke halaman (Authorization)
    public static function canViewAny(): bool
    {
        return auth()->user()->isSuperAdmin();
    }

    // Menghilangkan menu dari sidebar (UI Navigation)
    public static function shouldRegisterNavigation(): bool
    {
        return auth()->user()->isSuperAdmin();
    }
}
