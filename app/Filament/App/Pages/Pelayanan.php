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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
                Section::make('')
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

                        TextInput::make('nomor_wa')
                            ->label('WhatsApp')
                            ->tel()
                            ->required()
                            ->placeholder('Ketik nomor WA...')
                            ->datalist(DataClient::pluck('nomor_wa')->toArray())

                            // 🚀 PERBAIKAN 1: Gunakan live() biasa tanpa onBlur agar respon pencarian nomor lebih cepat 
                            // dan tambahkan debounce agar Livewire menunggu Anda selesai mengetik sesaat (500ms)
                            ->live(debounce: 500)
                            ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                if ($state) {
                                    $client = DataClient::where('nomor_wa', $state)->first();

                                    if ($client) {
                                        $formattedName = ucwords($client->nama);
                                        $set('nama', $formattedName);

                                        $services = $get('services') ?? [];
                                        foreach ($services as $key => $service) {
                                            $set("services.{$key}.nama_pelanggan", $formattedName);
                                        }
                                    }
                                    // 🚀 PERBAIKAN 2: JANGAN gunakan $set('nama', '') di sini jika data tidak ada!
                                    // Menghapus baris else { $set('nama', ''); } mencegah form nama terosongkan tiba-tiba saat Anda sedang mengetik nama baru.
                                }
                            }),

                        // 2. Form Nama Kontak
                        TextInput::make('nama')
                            ->label('Nama Kontak')
                            ->required()
                            ->extraInputAttributes(['style' => 'text-transform: capitalize;'])

                            // 🚀 PERBAIKAN 3: Ubah menjadi live(debounce: 500) agar perubahan nama ke repeater 
                            // dikirim secara halus tanpa merusak atau memotong teks yang sedang Anda ketik murni di keyboard
                            ->live(debounce: 800)
                            ->afterStateUpdated(function (?string $state, Set $set, Get $get) {
                                $formattedName = $state ? ucwords($state) : '';

                                $services = $get('services') ?? [];
                                foreach ($services as $key => $service) {
                                    $set("services.{$key}.nama_pelanggan", $formattedName);
                                }
                            }),
                    ])->columns(2),

                Repeater::make('services')
                    ->label('')
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

                            Section::make('')
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
                // 1. Simpan ke tabel ServiceMasuk
                $createdService = ServiceMasuk::create([
                    'category_id'    => $service['category_id'],
                    'nama_pelanggan' => $service['nama_pelanggan'],
                    'nama_barang'    => $service['nama_barang'],
                    'data_client_id' => $currentClient->id,
                    'tanggal_masuk'  => $service['tanggal_masuk'],
                    'kerusakan'      => $service['kerusakan'] ?? null,
                    'perlengkapan'   => $service['perlengkapan'] ?? [],
                    'keterangan'     => $service['keterangan'] ?? null,
                    'token'          => $service['token'],
                ]);

                // 2. Simpan ke tabel DataService
                \App\Models\DataService::create([
                    'category_id'    => $service['category_id'],
                    'data_client_id' => $currentClient->id,
                    'nama_pelanggan' => $service['nama_pelanggan'],
                    'nama_barang'    => $service['nama_barang'],
                    'tanggal_masuk'  => $service['tanggal_masuk'],
                    'kerusakan'      => $service['kerusakan'] ?? [], // Disimpan sebagai array/json sesuai struktur tabel
                    'perlengkapan'   => $service['perlengkapan'] ?? [],  // Disimpan sebagai array/json sesuai struktur tabel
                    'keterangan'     => $service['keterangan'] ?? null,
                ]);

                $this->serviceIds[] = $createdService->id;

                if ($this->servicePreview === null) {
                    $this->servicePreview = $createdService;
                }
            }
        });

        // Ambil data terbaru untuk WhatsApp
        $allServices = ServiceMasuk::with('category')->whereIn('id', $this->serviceIds)->get();

        $this->waUrl = '#';

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
                                $this->js("window.open('" . route('service.print.masuk', $id) . "', '_blank');");
                            }
                        }),

                    Action::make('wa_admin1')
                        ->label('📲 WhatsApp Admin 1')
                        ->color('success')
                        ->button()
                        ->action(function (Action $action) {
                            // 1. Cek apakah ada unit service yang baru disimpan di form ini
                            if (empty($this->serviceIds)) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Gagal Kirim')
                                    ->body('Data pelayanan belum tersimpan. Silakan simpan form terlebih dahulu.')
                                    ->danger()->send();
                                return;
                            }

                            // 2. Ambil daftar unit service yang baru saja diinput di halaman ini
                            $services = \App\Models\ServiceMasuk::whereIn('id', $this->serviceIds)->get();

                            // 3. Ambil data client langsung dari relasi unit service pertama (pasti akurat & pas)
                            $firstService = $services->first();
                            $client = $firstService ? $firstService->dataClient : null;

                            if (!$client) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Gagal Kirim')
                                    ->body('Data pelanggan tidak ditemukan.')
                                    ->danger()->send();
                                return;
                            }

                            // 4. Jalankan fungsi Fonnte
                            $this->sendWhatsapp($client, $services, config('services.fonnte.admin1'));

                            $action->success();
                        }),

                    Action::make('wa_admin2')
                        ->label('📲 WhatsApp Admin 2')
                        ->color('success')
                        ->button()
                        ->action(function (Action $action) {
                            // 1. Cek apakah ada unit service yang baru disimpan di form ini
                            if (empty($this->serviceIds)) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Gagal Kirim')
                                    ->body('Data pelayanan belum tersimpan. Silakan simpan form terlebih dahulu.')
                                    ->danger()->send();
                                return;
                            }

                            // 2. Ambil daftar unit service yang baru saja diinput di halaman ini
                            $services = \App\Models\ServiceMasuk::whereIn('id', $this->serviceIds)->get();

                            // 3. Ambil data client langsung dari relasi unit service pertama (pasti akurat & pas)
                            $firstService = $services->first();
                            $client = $firstService ? $firstService->dataClient : null;

                            if (!$client) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Gagal Kirim')
                                    ->body('Data pelanggan tidak ditemukan.')
                                    ->danger()->send();
                                return;
                            }

                            // 4. Jalankan fungsi Fonnte
                            $this->sendWhatsapp($client, $services, config('services.fonnte.admin2'));

                            $action->success();
                        })
                ])
                ->modalContent(fn() => view('filament.service.preview', [
                    'service' => $this->servicePreview,
                ])),
        ];
    }


    private function sendWhatsapp($client, $services, string $tokenFonnte): void
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

        // 3. 🛠️ PERULANGAN BARANG (Menggunakan loop murni kodemu)
        foreach ($items as $index => $item) {
            $isArr = is_array($item);

            $categoryName = $isArr
                ? (\App\Models\Category::find($item['category_id'] ?? null)?->category ?? 'Unit')
                : ($item->category->category ?? 'Unit');

            $namaBarang = $isArr ? ($item['nama_barang'] ?? '-') : ($item->nama_barang ?? '-');

            $tglMasukRaw = $isArr ? ($item['tanggal_masuk'] ?? null) : ($item->tanggal_masuk ?? null);
            $tglMasukText = $tglMasukRaw
                ? \Carbon\Carbon::parse($tglMasukRaw)->translatedFormat('d/m/Y')
                : '-';

            $kerusakan = $isArr ? ($item['kerusakan'] ?? '-') : ($item->kerusakan ?? '-');
            $kerusakanText = is_array($kerusakan) ? implode(', ', $kerusakan) : $kerusakan;

            $perlengkapan = $isArr ? ($item['perlengkapan'] ?? '-') : ($item->perlengkapan ?? '-');
            $perlengkapanText = is_array($perlengkapan) ? implode(', ', $perlengkapan) : $perlengkapan;
            $perlengkapanText = str_replace('_', ' ', $perlengkapanText);
            $perlengkapanText = ucwords($perlengkapanText);

            // $token = $isArr ? ($item['token'] ?? '') : ($item->token ?? '');
            // $linkTracking = url("/tracking/{$token}");

            $no = $index + 1;
            $pesan .= "*No. {$no}*\n";
            $pesan .= "Unit: {$categoryName} {$namaBarang}\n";
            $pesan .= "Tgl Masuk: {$tglMasukText}\n";
            $pesan .= "Trouble: {$kerusakanText}\n";
            $pesan .= "Kelengkapan: {$perlengkapanText}\n";
            // $pesan .= "Tracking: {$linkTracking}\n";
            $pesan .= "-----------------------------------\n";
        }

        // 4. penutup pesan (Hanya muncul 1x di paling bawah)
        $pesan .= "\nUntuk pengambilan unit akan kami infokan kembali dengan QR Code pengambilan.\n\n";
        $pesan .= "Hormat kami,\n*Acegroup Service Center*";

        // Nomor WA tujuan
        $nomor_wa = preg_replace('/[^0-9]/', '', $client->nomor_wa);
        if (str_starts_with($nomor_wa, '0')) {
            $nomor_wa = '62' . substr($nomor_wa, 1);
        }

        // 5. 🚀 HIT API FONNTE & NOTIFIKASI
        try {
            $response = Http::withHeaders([
                'Authorization' => $tokenFonnte, // 🛠️ GANTI ENV LAMA DENGAN VARIABEL INI
            ])->timeout(10)->post('https://api.fonnte.com/send', [
                'target'      => $nomor_wa,
                'message'     => $pesan,
                'countryCode' => '62',
            ]);

            $result = $response->json();

            if (isset($result['status']) && $result['status'] === true) {
                \Filament\Notifications\Notification::make()
                    ->title('WhatsApp Terkirim')
                    ->body('Nota tanda terima berhasil dikirim otomatis melalui Fonnte.')
                    ->success()->send();
            } else {
                \Filament\Notifications\Notification::make()
                    ->title('WhatsApp Gagal')
                    ->body('Fonnte gagal memproses pesan. Periksa kuota atau token Anda.')
                    ->danger()->send();
            }
        } catch (\Exception $e) {
            Log::error('Fonnte Error: ' . $e->getMessage());
            \Filament\Notifications\Notification::make()
                ->title('WhatsApp Gagal')
                ->body('Terjadi kesalahan jaringan server API.')
                ->danger()->send();
        }
    }
}
