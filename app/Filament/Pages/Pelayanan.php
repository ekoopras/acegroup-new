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
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Actions\Action;


class Pelayanan extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Pelayanan';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $title = '';
    public ?ServiceMasuk $servicePreview = null;
    public bool $showPreviewModal = false;
    public array $serviceIds = [];



    protected static string $view = 'filament.pages.pelayanan';



    /**
     * State form
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'tanggal_masuk' => now(),

            // ⭐ WAJIB untuk repeater
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
                section::make()
                    ->schema([
                        TextInput::make('nama')
                            ->label('Nama Client')
                            ->required(),

                        TextInput::make('nomor_wa')
                            ->label('Nomor WhatsApp')
                            ->tel()
                            ->required(),
                    ])->columns(2),


                section::make()
                    ->schema([

                        Repeater::make('services')
                            ->label('Barang / Service')
                            ->defaultItems(1)
                            ->minItems(1)
                            ->addActionLabel('+ Tambah Barang')
                            ->schema([

                                // GRID UTAMA DALAM REPEATER
                                Grid::make([
                                    'default' => 1,
                                    'md' => 3, // desktop 3 kolom
                                ])->schema([

                                    /**
                                     * =====================
                                     * KIRI (LEBIH LEBAR)
                                     * =====================
                                     */
                                    Section::make()
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 2,
                                        ])
                                        ->schema([

                                            // DATA BARANG
                                            Section::make()
                                                ->schema([
                                                    Select::make('category_id')
                                                        ->label('Kategori')
                                                        ->options(Category::pluck('category', 'id'))
                                                        ->searchable()
                                                        ->required(),

                                                    TextInput::make('nama_barang')
                                                        ->label('Nama Barang')
                                                        ->required(),

                                                    DatePicker::make('tanggal_masuk')
                                                        ->label('Tanggal Masuk')
                                                        ->required(),
                                                ])
                                                ->columns(3),

                                            // KERUSAKAN & KETERANGAN
                                            Section::make()
                                                ->schema([
                                                    Textarea::make('kerusakan')
                                                        ->label('Kerusakan')
                                                        ->rows(5),

                                                    Textarea::make('keterangan')
                                                        ->label('Keterangan')
                                                        ->rows(5),
                                                ])
                                                ->columns(2),
                                        ]),

                                    /**
                                     * =====================
                                     * KANAN
                                     * =====================
                                     */
                                    Section::make('')
                                        ->columnSpan([
                                            'default' => 1,
                                            'md' => 1,
                                        ])
                                        ->schema([
                                            CheckboxList::make('perlengkapan')
                                                ->label('')
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
                                                ->columns([
                                                    'default' => 2,
                                                    'md' => 1,
                                                ]),
                                        ]),
                                ]),
                            ])


                    ]),


            ]);
    }




    public function submit(): void
    {
        $this->form->validate();

        // reset state
        $this->servicePreview = null;
        $this->serviceIds = [];

        DB::transaction(function () {

            // 1️⃣ CLIENT (ANTI DUPLIKAT WA)
            $client = DataClient::firstOrCreate(
                ['nomor_wa' => $this->data['nomor_wa']],
                ['nama' => $this->data['nama']]
            );

            // 2️⃣ LOOP BARANG
            foreach ($this->data['services'] as $service) {

                $createdService = ServiceMasuk::create([
                    'category_id'   => $service['category_id'],
                    'nama_barang'   => $service['nama_barang'],
                    'nama_client'   => $client->nama,
                    'nomor_wa'      => $client->nomor_wa,
                    'tanggal_masuk' => $service['tanggal_masuk'],
                    'kerusakan'     => $service['kerusakan'] ?? null,
                    'perlengkapan'  => $service['perlengkapan'] ?? [],
                    'keterangan'    => $service['keterangan'] ?? null,
                ]);

                // simpan semua ID (buat print)
                $this->serviceIds[] = $createdService->id;

                // ambil satu untuk preview
                if ($this->servicePreview === null) {
                    $this->servicePreview = $createdService;
                }
            }
        });

        // ⛑️ AMAN DARI NULL
        if ($this->servicePreview) {
            $this->servicePreview->load('category');
        }

        Notification::make()
            ->title('Berhasil')
            ->body('Semua barang berhasil disimpan')
            ->success()
            ->send();

        // tampilkan modal preview
        $this->mountAction('preview');

        // reset form + default 1 barang
        $this->reset('data');

        $this->form->fill([
            'services' => [
                [
                    'tanggal_masuk' => now(),
                ],
            ],
        ]);
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

                    // 🖨 PRINT SEMUA BARANG (TAB BARU)
                    Action::make('print')
                        ->label('🖨 Print Semua')
                        ->action(function () {
                            foreach ($this->serviceIds as $id) {
                                $this->js(
                                    "window.open('" . route('service.print', $id) . "', '_blank');"
                                );
                            }
                        }),

                    // 📲 WHATSAPP
                    Action::make('wa')
                        ->label('📲 Kirim WhatsApp')
                        ->color('success')
                        ->url(fn() => $this->sendWhatsapp($this->servicePreview))
                        ->openUrlInNewTab(),

                ])
                ->modalContent(fn() => view('filament.service.preview', [
                    'service' => $this->servicePreview,
                ])),
        ];
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
