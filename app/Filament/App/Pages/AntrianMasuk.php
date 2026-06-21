<?php

namespace App\Filament\App\Pages;

use App\Models\ServiceMasuk;
use App\Models\ServiceProses;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Pages\Page;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Contracts\HasTable as HasTableContract;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Filament\Tables\Columns\Layout\View;
use Illuminate\Support\Carbon;

class AntrianMasuk extends Page implements HasTableContract
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Unit Masuk';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = '';

    protected static string $view = 'filament.app.pages.antrian-masuk';

    public function table(Table $table): Table
    {
        $query = ServiceMasuk::query(); // Sesuaikan nama model Anda

        // 2. Suntikkan logika pembatasan Kategori (Sama seperti getEloquentQuery kemarin)
        if (! auth()->user()->isSuperAdmin()) {
            $query->whereIn(
                'category_id',
                auth()->user()->category()->pluck('categories.id')
            );
        }

        return $table
            ->query($query)
            ->contentGrid([
                'default' => 1,
                'md' => 3,
                'xl' => 3,
            ])
            ->columns([
                Stack::make([


                    // 2. BODY KARTU
                    Stack::make([
                        Split::make([
                            TextColumn::make('nama_pelanggan')
                                ->extraAttributes(['class' => 'text-xs font-semibold font-xl text-slate-400 w-1/1'])
                                ->searchable(),
                            TextColumn::make('dataClient.nomor_wa')
                                ->searchable()->formatStateUsing(fn($state) => ucwords($state))
                                ->alignEnd()
                                ->extraAttributes(['class' => 'text-sm font-bold text-slate-800 dark:text-slate-200 w-2/3']),
                        ])->extraAttributes([
                            'class' => 'py-2 border-b border-slate-100 dark:border-transparent',
                            'style' => 'border-bottom-color: #a9a9a95e;'
                        ]),

                        Split::make([
                            TextColumn::make('nama_barang')
                                ->label('Unit Barang')
                                ->formatStateUsing(fn($record) => ($record->category?->category ?? '') . ' ' . $record->nama_barang)
                                ->searchable(query: function ($query, string $search) {
                                    $query->where(function ($q) use ($search) {
                                        $q->where('nama_barang', 'like', "%{$search}%")
                                            ->orWhereHas('category', function ($catQuery) use ($search) {
                                                $catQuery->where('category', 'like', "%{$search}%");
                                            });
                                    });
                                }),
                        ])->extraAttributes([
                            'class' => 'py-2 border-b border-slate-100 dark:border-transparent',
                            'style' => 'border-bottom-color: #a9a9a95e;'
                        ]),

                        Split::make([
                            TextColumn::make('kerusakan')
                                ->badge()
                                ->color('success')
                                ->separator(',')
                                ->extraAttributes(['class' => 'w-2/3 flex flex-wrap gap-1'])
                                ->searchable(),
                        ])->extraAttributes([
                            'class' => 'py-2 border-b border-slate-100 dark:border-transparent',
                            'style' => 'border-bottom-color: #a9a9a95e;'
                        ]),

                        Split::make([
                            TextColumn::make('nomor_surat')
                                ->fontFamily('mono')
                                ->searchable()
                                ->extraAttributes(['class' => 'text-slate-900 dark:text-white']),

                            TextColumn::make('tanggal_masuk')
                                ->date('d/m/Y')
                                ->alignEnd()
                                ->extraAttributes(['class' => 'text-xs text-slate-400 font-medium'])
                                ->searchable(),

                        ])->extraAttributes(['class' => 'py-2']),

                    ]),

                ])->space(0),
            ])
            ->defaultPaginationPageOption(12) // Set default awal ke 10 data
            ->paginationPageOptions([12])
            ->filters([])
            ->actions([
                Action::make('prosesUnit')
                    ->label('')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->button()
                    ->color('primary')
                    ->action(function ($record) {
                        \App\Models\ServiceProses::create([
                            'category_id'    => $record->category_id,
                            'data_client_id' => $record->data_client_id,
                            'nama_pelanggan' => $record->nama_pelanggan,
                            'nama_barang'    => $record->nama_barang,
                            'nomor_surat'    => $record->nomor_surat,
                            'qrcode'         => $record->qrcode,
                            'tanggal_masuk'  => $record->tanggal_masuk,
                            'kerusakan'      => $record->kerusakan,
                            'perlengkapan'   => $record->perlengkapan,
                            'keterangan'     => $record->keterangan,
                            'token'          => $record->token,
                            'log_status'     => [
                                [
                                    'status'     => 'Proses Cek',
                                    'user_id'    => auth()->id(),
                                    'tanggal'    => now()->toDateTimeString(),
                                    'keterangan' => 'Unit mulai dikerjakan oleh teknisi.',
                                ]
                            ],
                            'user_id'        => auth()->id(),
                        ]);

                        $record->delete();

                        \Filament\Notifications\Notification::make()
                            ->title('Unit Berhasil Diproses')
                            ->success()
                            ->send();
                    }),

                Action::make('print')
                    ->label('')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->button()
                    ->url(fn($record) => route('service.print.masuk', $record->id))
                    ->openUrlInNewTab(),

                Action::make('whatsapp')
                    ->label('')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->button()
                    ->url(function ($record) {
                        $client = $record->dataClient;
                        $categoryName = $record->category->category ?? 'Unit';
                        $kerusakanText = is_array($record->kerusakan) ? implode(', ', $record->kerusakan) : ($record->kerusakan ?? '-');
                        $perlengkapanText = is_array($record->perlengkapan) ? implode(', ', $record->perlengkapan) : ($record->perlengkapan ?? '-');
                        $linkTracking = url("/tracking/{$record->token}");

                        $pesan = "Asallamuallaikum *{$record->nama_pelanggan}*\n\n"
                            . "Service anda sudah kami terima. Berikut rincian unit anda:\n\n"
                            . "Unit: *{$categoryName} {$record->nama_barang}*\n"
                            . "No. Surat: {$record->nomor_surat}\n"
                            . "Trouble: {$kerusakanText}\n"
                            . "Kelengkapan: {$perlengkapanText}\n"
                            . "Tracking: {$linkTracking}\n"
                            . "----------------------------\n\n"
                            . "Untuk pengambilan unit akan kami infokan kembali dengan QR Code pengambilan.\n\n"
                            . "Hormat kami,\n*Acegroup Service Center*";

                        $nomor_wa = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $client->nomor_wa ?? ''));
                        return "https://api.whatsapp.com/send?phone={$nomor_wa}&text=" . urlencode($pesan);
                    })
                    ->openUrlInNewTab(),


                EditAction::make()
                    ->label('')
                    ->icon('heroicon-m-pencil-square')
                    ->color('warning')
                    ->button()
                    // Form skema modal edit jika diperlukan (sesuaikan dengan field di model ServiceMasuk kamu)
                    ->form([
                        Grid::make(3)->schema([
                            TextInput::make('nomor_surat')
                                ->label('Nomor Surat')
                                ->placeholder('Otomatis...')
                                ->disabled()
                                ->columnSpan(1),

                            Select::make('data_client_id')
                                ->label('Nama Client')
                                ->relationship('dataClient', 'nama')
                                ->required()
                                ->searchable()
                                ->disabled()
                                ->afterStateHydrated(function ($state, $set) {
                                    $client = \App\Models\DataClient::find($state);
                                    if ($client) {
                                        $set('nomor_wa', $client->nomor_wa);
                                    }
                                })
                                ->afterStateUpdated(function ($state, $set) {
                                    $client = \App\Models\DataClient::find($state);
                                    if ($client) {
                                        $set('nomor_wa', $client->nomor_wa);
                                    }
                                })
                                ->columnSpan(1),

                            TextInput::make('nomor_wa')
                                ->label('Nomor WhatsApp')
                                ->tel()
                                ->disabled()
                                ->dehydrated(false)
                                ->placeholder('628xxx')
                                ->helperText('Gunakan format 62')
                                ->columnSpan(1),
                        ]),

                        Grid::make(3)->schema([

                            // SISI KIRI (BARANG & KERUSAKAN) - 2 Kolom
                            Group::make([
                                Section::make('Detail Unit')
                                    ->schema([
                                        Grid::make(1)->schema([
                                            TextInput::make('nama_pelanggan')
                                                ->label('Nama Pelanggan')
                                                ->required()
                                                ->extraInputAttributes(['style' => 'text-transform: capitalize;']),
                                        ]),
                                        Grid::make(2)->schema([
                                            Select::make('category_id')
                                                ->label('Kategori')
                                                ->relationship('category', 'category')
                                                ->searchable()
                                                ->required(),

                                            TextInput::make('nama_barang')
                                                ->label('Nama Barang')
                                                ->required()
                                                ->extraInputAttributes(['style' => 'text-transform: capitalize;']),
                                        ]),

                                        DatePicker::make('tanggal_masuk')
                                            ->label('Tanggal Masuk')
                                            ->default(now())
                                            ->required(),

                                        Textarea::make('kerusakan')
                                            ->label('Deskripsi Kerusakan')
                                            ->placeholder('Contoh: Laptop mati total, sering restart sendiri...')
                                            ->required()
                                            ->rows(3),

                                        Textarea::make('keterangan')
                                            ->label('Keterangan Tambahan')
                                            ->placeholder('Catatan khusus teknisi...')
                                            ->rows(2),
                                    ]),
                            ])->columnSpan(2),

                            // SISI KANAN (PERLENGKAPAN & QR) - 1 Kolom
                            Group::make([
                                Section::make('Fisik & Validasi')
                                    ->schema([
                                        CheckboxList::make('perlengkapan')
                                            ->label('Perlengkapan yang Dibawa')
                                            ->options([
                                                'tas' => 'Tas',
                                                'adaptor_charger' => 'Adaptor Charger',
                                                'kabel_power' => 'Kabel Power',
                                                'kabel_usb_print' => 'Kabel USB Print',
                                                'kardus' => 'Kardus',
                                                'battrai' => 'Baterai',
                                                'kesing_kanan' => 'Kesing R',
                                                'kesing_kiri' => 'Kesing L',
                                                'usb_data' => 'USB Data',
                                            ])
                                            ->columns(1) // Satu kolom ke bawah agar rapi di sisi kanan
                                            ->bulkToggleable(), // Fitur pilih semua

                                        ViewField::make('qrcode')
                                            ->label('QR Code Tracking')
                                            ->view('filament.components.qrcode'),
                                    ]),
                            ])->columnSpan(1),
                        ]),

                    ])
                    ->slideOver()
                    ->modalWidth('full'),
            ]);
    }

    public function prosesUnit(ServiceMasuk $record)
    {
        // 1. Pindahkan data ke tabel ServiceProses
        ServiceProses::create([
            'category_id'    => $record->category_id,
            'data_client_id' => $record->data_client_id,
            'nama_pelanggan' => $record->nama_pelanggan,
            'nama_barang'    => $record->nama_barang,
            'nomor_surat'    => $record->nomor_surat,
            'qrcode'         => $record->qrcode,
            'tanggal_masuk'  => $record->tanggal_masuk,
            'kerusakan'      => $record->kerusakan,
            'perlengkapan'   => $record->perlengkapan,
            'keterangan'     => $record->keterangan,
            'token'          => $record->token,

            // Log status awal pengerjaan mekanik/teknisi
            'log_status'     => [
                [
                    'status'     => 'Proses Cek',
                    'tanggal'    => now()->toDateTimeString(),
                    'keterangan' => 'Unit mulai dikerjakan oleh teknisi via Mobile.',
                ]
            ],

            'user_id'        => auth()->id(),
        ]);

        // 2. Hapus data asli dari antrean ServiceMasuk
        $record->delete();

        // 3. Berikan notifikasi sukses khas Filament v3
        \Filament\Notifications\Notification::make()
            ->title('Unit Berhasil Diproses')
            ->body("Nomor surat {$record->nomor_surat} telah dipindahkan ke daftar pengerjaan.")
            ->success()
            ->send();

        // 4. Perbarui state halaman antrean mobile agar kartu langsung hilang dari list
        // (Otomatis mendeteksi perubahan karena ini adalah halaman Livewire Component)
        $this->dispatch('refreshTable');
    }
}
