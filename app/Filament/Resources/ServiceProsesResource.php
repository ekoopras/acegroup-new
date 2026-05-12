<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceProsesResource\Pages;
use App\Filament\Resources\ServiceProsesResource\RelationManagers;
use App\Models\ServiceJadi;
use App\Models\ServiceProses;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\Components\Tabs\Tab;
use Illuminate\Support\Facades\DB;

class ServiceProsesResource extends Resource
{
    protected static ?string $model = ServiceProses::class;
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Service Proses';
    protected static ?string $pluralLabel = 'Service Proses';
    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Section::make('Log Perkembangan')
                    ->description('Tambahkan riwayat pengecekan dan pengerjaan di sini')
                    ->schema([
                        Repeater::make('log_status')
                            ->label('Update Progres')
                            ->schema([
                                Select::make('status')
                                    ->options([
                                        'Proses Cek' => 'Proses Cek',
                                        'Pending' => 'Pending (Menunggu Part/Konfirmasi)',
                                        'Deal' => 'Deal (Pengerjaan Disetujui)',
                                        'Proses Pengerjaan' => 'Proses Pengerjaan',
                                        'Trial' => 'Trial',
                                        'Selesai' => 'Selesai',
                                    ])
                                    ->required()
                                    ->native(false),

                                DateTimePicker::make('tanggal')
                                    ->default(now())
                                    ->required(),

                                Select::make('teknisi_id')
                                    ->label('Teknisi')
                                    ->options(User::all()->pluck('name', 'id')) // Mengambil nama dari UserResource
                                    ->default(auth()->id())
                                    ->searchable()
                                    ->native(false),

                                Textarea::make('keterangan')
                                    ->placeholder('Contoh: Sedang mengganti IC Power...')
                                    ->rows(2)
                                    ->columnSpanFull(),


                            ])
                            ->columns(3)
                            ->collapsible() // Bisa diciutkan agar rapi
                            ->cloneable() // Mempermudah teknisi jika keterangan mirip
                            ->addActionLabel('Tambah Update Baru')
                            ->reorderableWithButtons(), // Urutan bisa diatur
                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tables\Columns\TextColumn::make('barang')
                //     ->label('Barang')
                //     ->html()
                //     ->getStateUsing(
                //         fn($record) =>
                //         '<strong>' . e($record->category->category) . '</strong><br>' .
                //             e($record->nama_barang)
                //     )
                //     ->searchable(),

                Tables\Columns\TextColumn::make('category.category')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama_barang')
                    ->searchable(),

                Tables\Columns\TextColumn::make('dataClient.nama')
                    ->label('Nama Client')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_masuk')
                    ->label('Masuk')
                    ->date('d M Y')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kerusakan')
                    ->label('Daftar Kerusakan')
                    ->badge()
                    ->color('danger')
                    ->listWithLineBreaks()
                    ->wrap(),

                Tables\Columns\TextColumn::make('log_status')
                    ->label('Status Terakhir')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        // Mengambil array log_status
                        $logs = $record->log_status;

                        // Pastikan logs adalah array dan tidak kosong
                        if (is_array($logs) && !empty($logs)) {
                            // Mengambil elemen terakhir dari array
                            $lastLog = end($logs);
                            return $lastLog['status'] ?? '-';

                            $teknisiName = User::find($lastLog['teknisi_id'])?->name ?? 'No Name';
                            return "$status ($teknisiName)";
                        }

                        return 'Belum ada status';
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Proses Cek', 'Proses Pengerjaan' => 'warning',
                        'Pending' => 'danger',
                        'Deal', 'Selesai' => 'success',
                        default => 'gray',
                    })
                    // Opsional: Menampilkan keterangan terakhir di bawah status sebagai info tambahan
                    ->description(function ($record) {
                        $logs = $record->log_status;
                        if (is_array($logs) && !empty($logs)) {
                            $lastLog = end($logs);
                            return $lastLog['keterangan'] ?? '';
                        }
                        return null;
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('teknisi_terakhir')
                    ->label('Teknisi')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(function ($record) {
                        $logs = $record->log_status;
                        if (is_array($logs) && !empty($logs)) {
                            $lastLog = end($logs);
                            // Ambil ID teknisi dari log terakhir
                            $teknisiId = $lastLog['teknisi_id'] ?? null;

                            if ($teknisiId) {
                                return \App\Models\User::find($teknisiId)?->name ?? 'Anonim';
                            }
                        }
                        return 'Belum Ditentukan';
                    }),

                // Tables\Columns\TextColumn::make('token')
                //     ->label('Tracking Link')
                //     ->formatStateUsing(fn($state) => route('tracking.check', ['token' => $state]))
                //     ->copyable() // Agar bisa diklik untuk copy link
                //     ->color('primary')
                //     ->icon('heroicon-o-link'),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button(),
                Tables\Actions\Action::make('jadi')
                    ->label('Jadi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([

                        Select::make('garansi')
                            ->options([
                                '2_minggu' => '2 Minggu',
                                '1_bulan' => '1 Bulan',
                                '2_bulan' => '2 Bulan',
                                '3_bulan' => '3 Bulan',
                                '4_bulan' => '4 Bulan',
                                '5_bulan' => '5 Bulan',
                                '6_bulan' => '6 Bulan',
                                '1_tahun' => '1 Tahun',
                                '2_tahun' => '2 Tahun',
                                'None' => 'None',
                            ])
                            ->required(),

                        Repeater::make('services')
                            ->label('Daftar Service')
                            ->schema([
                                TextInput::make('service')
                                    ->label('Jenis Service')
                                    ->required(),

                                TextInput::make('biaya')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {

                                        $services = $get('../../services') ?? [];
                                        $subtotal = collect($services)->sum('biaya');

                                        $potongan = $get('../../potongan_biaya') ?? 0;

                                        $set('../../total_biaya', max($subtotal - $potongan, 0));
                                    }),
                            ])
                            ->columns(2)
                            ->minItems(1),

                        // 🔥 Tambahan Potongan
                        TextInput::make('potongan_biaya')
                            ->label('Potongan Biaya')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('Rp')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {

                                $services = $get('services') ?? [];
                                $subtotal = collect($services)->sum('biaya');

                                $potongan = $state ?? 0;

                                $set('total_biaya', max($subtotal - $potongan, 0));
                            }),

                        TextInput::make('total_biaya')
                            ->label('Total Biaya')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly()
                            ->dehydrated(true),


                    ])

                    ->action(function ($record, array $data) {
                        // 1. Ambil riwayat log lama & tambah status "Selesai"
                        $riwayatLengkap = $record->log_status ?? [];
                        $riwayatLengkap[] = [
                            'status'     => 'Selesai',
                            'tanggal'    => now()->toDateTimeString(),
                            'keterangan' => 'Unit telah selesai dikerjakan dan siap diambil. Garansi: ' . ($data['garansi'] == 'None' ? 'Tanpa Garansi' : $data['garansi']),
                        ];

                        // 2. Hitung total biaya
                        $subtotal = collect($data['services'] ?? [])->sum('biaya');
                        $potongan = $data['potongan_biaya'] ?? 0;
                        $total = max($subtotal - $potongan, 0);

                        // 3. Pindahkan ke ServiceJadi (Gunakan DB Transaction agar aman)
                        $jadi = DB::transaction(function () use ($record, $data, $riwayatLengkap, $total, $potongan) {
                            $newEntry = \App\Models\ServiceJadi::create([
                                'category_id'     => $record->category_id,
                                'data_client_id'  => $record->data_client_id,
                                'nama_barang'     => $record->nama_barang,
                                'nomor_surat'     => $record->nomor_surat,
                                'qrcode'          => $record->qrcode,
                                'token'           => $record->token,
                                'tanggal_masuk'   => $record->tanggal_masuk,
                                'tanggal_selesai' => now(),
                                'garansi'         => $data['garansi'],
                                'services'        => $data['services'],
                                'potongan_biaya'  => $potongan,
                                'total_biaya'     => $total,
                                'log_status'      => $riwayatLengkap,
                                'teknisi_id'      => auth()->id(),
                            ]);

                            $record->delete();
                            return $newEntry;
                        });

                        // 4. Siapkan Link & Pesan WhatsApp
                        $namaPelanggan = $jadi->dataClient->nama ?? 'Pelanggan';
                        $nomorWA = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $jadi->dataClient->nomor_wa));
                        $linkTracking = url("/tracking/{$jadi->token}");

                        $pesan = "Asallamuallaikum {$namaPelanggan}\n\n" .
                            "Unit service Anda telah *SELESAI* dikerjakan.\n\n" .
                            "Unit: {$jadi->nama_barang}\n" .
                            "Total Biaya: Rp " . number_format($total, 0, ',', '.') . "\n" .
                            "Garansi: " . ($jadi->garansi == 'None' ? 'Tanpa Garansi' : $jadi->garansi) . "\n\n" .
                            "Silakan ambil unit Anda dengan menunjukkan *QR CODE* pengambilan pada link berikut:\n" .
                            "{$linkTracking}\n\n" .
                            "Hormat kami,\nAcegroup Service Center";

                        $waUrl = "https://wa.me/{$nomorWA}?text=" . urlencode($pesan);

                        // 5. Kirim Notifikasi ke Teknisi dengan Tombol Kirim WA
                        \Filament\Notifications\Notification::make()
                            ->title('Unit Berhasil Diselesaikan')
                            ->body('Data telah dipindahkan ke rak "Jadi". Klik tombol di bawah untuk kirim WA.')
                            ->success()
                            ->persistent() // Agar notifikasi tidak hilang sendiri sebelum diklik
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('kirim_wa')
                                    ->label('Kirim WhatsApp')
                                    ->icon('heroicon-o-chat-bubble-left-right')
                                    ->color('success')
                                    ->url($waUrl)
                                    ->openUrlInNewTab(),
                            ])
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->button(),

                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->form([
                        Forms\Components\Select::make('alasan_batal')
                            ->label('Alasan Pembatalan')
                            ->options([
                                'Part Kosong' => 'Part Kosong / Tidak Tersedia',
                                'User Tidak Deal' => 'User Tidak Setuju Biaya (Cancel User)',
                                'Tidak Bisa Diperbaiki' => 'Kondisi Unit Tidak Memungkinkan (Gagal)',
                                'Resiko Terlalu Tinggi' => 'Resiko Terlalu Tinggi',
                            ])
                            ->required(),

                        Forms\Components\Textarea::make('catatan_tambahan')
                            ->label('Catatan Teknisi')
                            ->placeholder('Jelaskan alasan teknis di sini...')
                            ->rows(3),

                        Forms\Components\TextInput::make('total_biaya')
                            ->label('Total Biaya')
                            ->default(0)
                            ->prefix('Rp')
                            ->disabled() // Dikunci karena Free
                            ->dehydrated(true),
                    ])
                    ->action(function ($record, array $data) {
                        // 1. Ambil riwayat log lama & tambah status "Cancel / Gagal"
                        $riwayatLengkap = $record->log_status ?? [];
                        $riwayatLengkap[] = [
                            'status'     => 'Cancel / Gagal',
                            'tanggal'    => now()->toDateTimeString(),
                            'keterangan' => 'Unit tidak lanjut pengerjaan: ' . $data['alasan_batal'] . '. ' . ($data['catatan_tambahan'] ?? ''),
                        ];

                        // 2. Pindahkan ke ServiceJadi dengan biaya 0 (DB Transaction agar aman)
                        $jadi = DB::transaction(function () use ($record, $data, $riwayatLengkap) {
                            $newEntry = \App\Models\ServiceJadi::create([
                                'category_id'     => $record->category_id,
                                'data_client_id'  => $record->data_client_id,
                                'nama_barang'     => $record->nama_barang,
                                'nomor_surat'     => $record->nomor_surat,
                                'qrcode'          => $record->qrcode,
                                'token'           => $record->token,
                                'tanggal_masuk'   => $record->tanggal_masuk,
                                'tanggal_selesai' => now(),
                                'garansi'         => 'None', // Otomatis tanpa garansi
                                'services'        => [['service' => 'Pembatalan: ' . $data['alasan_batal'], 'biaya' => 0]], // Input riwayat service kosong
                                'potongan_biaya'  => 0,
                                'total_biaya'     => 0, // Paksa 0
                                'log_status'      => $riwayatLengkap,
                                'teknisi_id'      => auth()->id(),
                            ]);

                            $record->delete(); // Hapus dari proses
                            return $newEntry;
                        });

                        // 3. Siapkan Link & Pesan WhatsApp Khusus Pembatalan
                        $namaPelanggan = $jadi->dataClient->nama ?? 'Pelanggan';
                        $nomorWA = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $jadi->dataClient->nomor_wa));
                        $linkTracking = url("/tracking/{$jadi->token}");

                        $pesan = "Asallamuallaikum {$namaPelanggan}\n\n" .
                            "Kami menginfokan bahwa unit service Anda *DIBATALKAN/GAGAL*.\n\n" .
                            "Unit: {$jadi->nama_barang}\n" .
                            "Alasan: {$data['alasan_batal']}\n" .
                            "Biaya: *FREE (Rp 0)*\n\n" .
                            "Silakan ambil unit Anda kembali dengan menunjukkan *QR CODE* pengambilan pada link berikut:\n" .
                            "{$linkTracking}\n\n" .
                            "Hormat kami,\nAcegroup Service Center";

                        $waUrl = "https://wa.me/{$nomorWA}?text=" . urlencode($pesan);

                        // 4. Notifikasi Berhasil
                        \Filament\Notifications\Notification::make()
                            ->title('Unit Dibatalkan')
                            ->body('Data telah dipindahkan ke rak "Jadi" dengan biaya Rp 0.')
                            ->danger() // Warna merah untuk indikasi cancel
                            ->persistent()
                            ->actions([
                                \Filament\Notifications\Actions\Action::make('kirim_wa')
                                    ->label('Kirim WhatsApp')
                                    ->icon('heroicon-o-chat-bubble-left-right')
                                    ->color('success')
                                    ->url($waUrl)
                                    ->openUrlInNewTab(),
                            ])
                            ->send();
                    })
                    ->requiresConfirmation()
                    ->button(),

            ])

            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceProses::route('/'),
            //'create' => Pages\CreateServiceProses::route('/create'),
            //'edit' => Pages\EditServiceProses::route('/{record}/edit'),
        ];
    }
}
