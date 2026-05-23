<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;

use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\DB;
use App\Models\DataClient;
use App\Models\ServiceMasuk;
use App\Models\Category;
use App\Models\Kerusakan;
use App\Services\PrintService;
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
use Illuminate\Support\Facades\URL;

class Pelayanan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-check';
    protected static ?string $navigationLabel = 'Pelayanan';
    protected static ?string $title = 'Input Pelayanan Service';

    protected static string $view = 'filament.app.pages.pelayanan';

    public function getTitle(): string
    {
        return '';
    }


    public ?ServiceMasuk $servicePreview = null;
    public array $serviceIds = [];
    public ?string $waUrl = null; // Properti untuk menampung link WA

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
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if ($state) {
                                    $client = DataClient::find($state);
                                    if ($client) {
                                        $formattedName = ucwords($client->nama);

                                        // 1. Isi form utama di luar repeater
                                        $set('nama', $formattedName);
                                        $set('nomor_wa', $client->nomor_wa);

                                        // 2. 🛠️ SINKRONKAN KE REPEATER: Ambil baris repeater yang sedang aktif saat ini
                                        $services = $get('services') ?? [];
                                        foreach ($services as $key => $service) {
                                            $set("services.{$key}.nama_pelanggan", $formattedName);
                                        }
                                    }
                                }
                            })
                            ->columnSpanFull(),

                        TextInput::make('nama')
                            ->label('Nama Kontak')
                            ->required()
                            ->extraInputAttributes(['style' => 'text-transform: capitalize;'])
                            ->live(debounce: 500)
                            ->afterStateUpdated(function (string $state, Set $set, Get $get) {
                                $formattedName = ucwords($state);
                                $services = $get('services') ?? [];
                                foreach ($services as $key => $service) {
                                    $set("services.{$key}.nama_pelanggan", $formattedName);
                                }
                            }),

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
                                    Grid::make(1)->schema([
                                        TextInput::make('nama_pelanggan')
                                            ->label('Nama Pelanggan')
                                            ->required()
                                            ->extraInputAttributes(['style' => 'text-transform: capitalize;'])
                                            // Menjaga agar jika admin klik "+ Tambah Barang" baru, namanya langsung terisi otomatis
                                            ->default(fn(Get $get) => ucwords($get('../../nama') ?? '')),
                                    ]),
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
                    'nama_pelanggan'   => $service['nama_pelanggan'],
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
                ->slideOver()
                ->modalWidth('full')
                ->modalActions([
                    Action::make('print')
                        ->label('🖨 Print Local')
                        ->action(function () {
                            foreach ($this->serviceIds as $id) {
                                $this->js("window.open('" . route('service.print', $id) . "', '_blank');");
                            }
                        }),

                    Action::make('print_wifi')
                        ->label('🖨 Print WiFi')
                        ->color('success')
                        ->action(function () {

                            $json = json_encode([
                                'id' => $this->servicePreview->id,
                                'nama_pelanggan' => $this->servicePreview->nama_pelanggan ?? '-',
                                'nomor_wa' => $this->servicePreview->dataClient->nomor_wa ?? '-',
                                'category' => $this->servicePreview->category->category ?? '-',
                                'barang' => $this->servicePreview->nama_barang ?? '-',
                                'tanggal_masuk' => $this->servicePreview->tanggal_masuk ?? '-',
                                'kerusakan' => $this->servicePreview->kerusakan ?? '-',
                                'keterangan' => $this->servicePreview->keterangan ?? '-',
                                'perlengkapan' => $this->servicePreview->perlengkapan ?? '-',
                                'pdf_url' => route('service.print', $this->servicePreview->id),
                            ]);

                            $this->js("
                                const data = $json;

                                console.log('PRINT WIFI DATA:', data);

                                fetch('http://192.168.1.111:5000/print', {
                                    method: 'POST',
                                    headers: {
                                        'Content-Type': 'application/json'
                                    },
                                    body: JSON.stringify(data)
                                })
                                .then(response => response.json())
                                .then(result => {

                                    console.log('PRINT RESULT:', result);

                                    if(result.status === 'success') {
                                        alert('Print berhasil');
                                    } else {
                                        alert('Print gagal');
                                    }

                                })
                                .catch(error => {

                                    console.error('PRINT ERROR:', error);

                                    alert('Tidak dapat terhubung ke STB / Printer');

                                });
                            ");
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
        // Pastikan berupa array/koleksi yang bisa di-loop
        $items = $services ?? [];

        // 1. 🛠️ CARI NAMA PELANGGAN (Hanya dicari 1x sebelum masuk perulangan barang)
        $namaPelanggan = null;
        foreach ($items as $item) {
            if (is_array($item) && !empty($item['nama_pelanggan'])) {
                $namaPelanggan = $item['nama_pelanggan'];
                break;
            } elseif (is_object($item) && !empty($item->nama_pelanggan)) {
                $namaPelanggan = $item->nama_pelanggan;
                break;
            }
        }

        // Fallback jika baris repeater kosong semua
        if (empty($namaPelanggan)) {
            $namaPelanggan = $client->nama ?? ($client->dataClient->nama ?? 'Pelanggan');
        }

        $namaPelanggan = ucwords($namaPelanggan);

        // 2. 🛠️ BERIKAN SAPAAN (Hanya muncul 1x di paling atas pesan)
        $pesan = "Asallamuallaikum *{$namaPelanggan}*\n\n";
        $pesan .= "Service anda sudah kami terima. Berikut daftar unit anda:\n\n";

        // 3. 🛠️ PERULANGAN BARANG (Hanya bagian ini yang akan diulang-ulang sesuai jumlah barang)
        foreach ($items as $index => $item) {

            // Cek apakah $item berbentuk array atau object (Model) agar tidak memicu error baru
            $isArr = is_array($item);

            $categoryName = $isArr
                ? (\App\Models\Category::find($item['category_id'] ?? null)?->category ?? 'Unit')
                : ($item->category->category ?? 'Unit');

            $namaBarang = $isArr ? ($item['nama_barang'] ?? '-') : ($item->nama_barang ?? '-');

            $kerusakan = $isArr ? ($item['kerusakan'] ?? '-') : ($item->kerusakan ?? '-');
            $kerusakanText = is_array($kerusakan) ? implode(', ', $kerusakan) : $kerusakan;

            $perlengkapan = $isArr ? ($item['perlengkapan'] ?? '-') : ($item->perlengkapan ?? '-');
            $perlengkapanText = is_array($perlengkapan) ? implode(', ', $perlengkapan) : $perlengkapan;

            $token = $isArr ? ($item['token'] ?? '') : ($item->token ?? '');
            $linkTracking = url("/tracking/{$token}");

            $no = $index + 1;
            $pesan .= "*No. {$no}*\n";
            $pesan .= "Unit: {$categoryName} {$namaBarang}\n";
            $pesan .= "Trouble: {$kerusakanText}\n";
            $pesan .= "Kelengkapan: {$perlengkapanText}\n";
            $pesan .= "Tracking: {$linkTracking}\n";
            $pesan .= "----------------------------\n";
        }

        // 4. penutup pesan (Hanya muncul 1x di paling bawah)
        $pesan .= "\nUntuk pengambilan unit akan kami infokan kembali dengan QR Code pengambilan.\n\n";
        $pesan .= "Hormat kami,\n*Acegroup Service Center*";

        // Nomor WA tujuan
        $nomor_wa = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $client->nomor_wa));

        return "https://api.whatsapp.com/send?phone={$nomor_wa}&text=" . urlencode($pesan);
    }
}
