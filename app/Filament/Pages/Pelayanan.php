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

class Pelayanan extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Pelayanan';
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
                        Select::make('search_client')
                            ->label('Cari Client (Nama / Nomor WA)')
                            ->placeholder('Ketik untuk mencari...')
                            ->searchable()
                            ->preload()
                            // Ambil data dari tabel DataClient
                            ->options(DataClient::all()->mapWithKeys(function ($client) {
                                return [$client->id => "{$client->nama} - {$client->nomor_wa}"];
                            }))
                            ->live()
                            // LOGIKA AUTO-FILL: Saat diklik, isi form nama dan nomor_wa di bawah
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
                                                        ->live()
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
                                                    Select::make('kerusakan')
                                                        ->label('Daftar Kerusakan')
                                                        ->multiple() // Mengizinkan pilih banyak
                                                        ->searchable()
                                                        ->preload()
                                                        ->options(function (Get $get) {
                                                            $categoryId = $get('category_id');
                                                            if (! $categoryId) return [];

                                                            return Kerusakan::where('category_id', $categoryId)
                                                                ->pluck('nama_kerusakan', 'nama_kerusakan');
                                                        })
                                                        // Munculkan modal tambah data saat klik ikon plus (+)
                                                        ->createOptionForm([
                                                            TextInput::make('nama_kerusakan')
                                                                ->label('Nama Kerusakan Baru')
                                                                ->required(),
                                                        ])
                                                        // Logika penyimpanan data dari modal ke tabel master
                                                        ->createOptionUsing(function (array $data, Get $get) {
                                                            $categoryId = $get('category_id');

                                                            if (! $categoryId) return null;

                                                            $new = Kerusakan::create([
                                                                'category_id' => $categoryId,
                                                                'nama_kerusakan' => $data['nama_kerusakan'],
                                                            ]);

                                                            return $new->nama_kerusakan;
                                                        })
                                                        ->required(),

                                                    Textarea::make('keterangan')
                                                        ->label('Keterangan')
                                                        ->rows(5),

                                                    Hidden::make('token')
                                                        ->default(fn() => str()->random(32)),
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
                    'data_client_id' => $client->id,
                    'tanggal_masuk' => $service['tanggal_masuk'],
                    'kerusakan'     => $service['kerusakan'] ?? null,
                    'perlengkapan'  => $service['perlengkapan'] ?? [],
                    'keterangan'    => $service['keterangan'] ?? null,
                    'token'   => $service['token'],
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
        $service->load('dataClient', 'category');

        $kerusakanText = is_array($service->kerusakan)
            ? implode(', ', $service->kerusakan)
            : $service->kerusakan;

        $text = urlencode(
            "Halo {$service->dataClient->nama},\n\n" .
                "Service Anda sudah kami terima.\n\n" .
                "Kategori: {$service->category->category}\n" .
                "Kerusakan: {$kerusakanText}\n" .
                "Tanggal: {$service->tanggal_masuk->format('d-m-Y')}\n\n" .
                "Terima kasih 🙏"
        );

        return "https://wa.me/{$service->dataClient->nomor_wa}?text={$text}";
    }
}
