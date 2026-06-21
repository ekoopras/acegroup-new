<?php

namespace App\Filament\App\Actions\UnitProsesAction;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Support\RawJs;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\DB;

class JadiAction
{
    public static function make(): Action
    {
        return Action::make('jadi')
            ->label('')
            ->icon('heroicon-o-check-circle')
            ->color('info')
            // 🛠️ FIX: Gunakan mountUsing untuk Table Action di Filament v3
            ->mountUsing(function (\Filament\Forms\Form $form, $record) {
                $logs = $record->log_status;

                if (is_string($logs)) {
                    $logs = json_decode($logs, true);
                }

                $rincianTerakhir = [];
                if (is_array($logs) && !empty($logs)) {
                    // Balik urutan log untuk mencari rincian biaya paling terbaru yang diinput teknisi
                    $reversedLogs = array_reverse($logs);
                    foreach ($reversedLogs as $log) {
                        if (isset($log['rincian_biaya']) && is_array($log['rincian_biaya'])) {
                            $rincianTerakhir = $log['rincian_biaya'];
                            break;
                        }
                    }
                }

                // Mapping dari kolom 'kerusakan' (kemarin) menjadi kolom 'service' (sekarang)
                $servicesMapped = [];
                foreach ($rincianTerakhir as $item) {
                    $servicesMapped[] = [
                        'service' => $item['kerusakan'] ?? '',
                        'biaya'   => $item['biaya'] ?? 0,
                        'garansi' => $item['garansi'] ?? '',
                        'kode' => $item['kode'] ?? '',
                    ];
                }

                // Hitung total biaya awal untuk langsung ditampilkan di input total_biaya
                $totalBiayaAwal = collect($servicesMapped)->sum('biaya');

                // Isi data ke form modal 'jadi' secara live
                $form->fill([
                    'services'    => $servicesMapped,
                    'total_biaya' => $totalBiayaAwal,
                    'kirim_wa_via' => 'none', // Set default toggle ke none
                ]);
            })
            ->form([

                // 🛠️ TAMBAHAN INTEGRASI: Opsi Pengiriman Fonnte via ToggleButtons
                \Filament\Forms\Components\ToggleButtons::make('kirim_wa_via')
                    ->label('Opsi Pengiriman Notifikasi / Nota Selesai WhatsApp:')
                    ->options([
                        'none' => 'None',
                        'admin1' => 'Admin 1',
                        'admin2' => 'Admin 2',
                    ])
                    ->colors([
                        'none' => 'success',
                        'admin1' => 'success',
                        'admin2' => 'success',
                    ])
                    ->icons([
                        'none' => 'heroicon-m-x-circle',
                        'admin1' => 'heroicon-m-paper-airplane',
                        'admin2' => 'heroicon-m-paper-airplane',
                    ])
                    ->inline()
                    ->required(),

                Repeater::make('services')
                    ->label('Daftar Service')
                    ->schema([
                        TextInput::make('service')
                            ->label('Jenis Service')
                            ->required(),

                        TextInput::make('biaya')
                            ->numeric()
                            ->required()
                            ->numeric()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $services = $get('../../services') ?? [];
                                $subtotal = collect($services)->sum('biaya');
                                $potongan = $get('../../potongan_biaya') ?? 0;

                                $set('../../total_biaya', max($subtotal - $potongan, 0));
                            }),

                        TextInput::make('garansi')
                            ->label('Garansi')
                            ->placeholder('1 Bulan'),
                        TextInput::make('kode')
                            ->label('kode barang'),
                    ])
                    ->columns(4)
                    ->minItems(1),

                TextInput::make('total_biaya')
                    ->label('Total Biaya')
                    ->numeric()
                    ->prefix('Rp')
                    ->readOnly()
                    ->dehydrated(true),
            ])
            ->action(function ($record, array $data, \Filament\Tables\Actions\Action $action) {
                // 1. Ambil riwayat log lama & tambah status "Selesai"
                $riwayatLengkap = $record->log_status ?? [];
                if (is_string($riwayatLengkap)) {
                    $riwayatLengkap = json_decode($riwayatLengkap, true);
                }

                $riwayatLengkap[] = [
                    'status'     => 'Selesai',
                    'tanggal'    => now()->toDateTimeString(),
                ];

                // 2. Hitung total biaya akhir
                $subtotal = collect($data['services'] ?? [])->sum('biaya');
                $potongan = $data['potongan_biaya'] ?? 0;
                $total = max($subtotal - $potongan, 0);

                // 3. Pindahkan ke ServiceJadi (Gunakan DB Transaction agar aman)
                $jadi = DB::transaction(function () use ($record, $data, $riwayatLengkap, $total, $potongan) {
                    $newEntry = \App\Models\ServiceJadi::create([
                        'category_id'     => $record->category_id,
                        'data_client_id'  => $record->data_client_id,
                        'nama_pelanggan'  => $record->nama_pelanggan,
                        'nama_barang'     => $record->nama_barang,
                        'nomor_surat'     => $record->nomor_surat,
                        'qrcode'          => $record->qrcode,
                        'token'           => $record->token,
                        'tanggal_masuk'   => $record->tanggal_masuk,
                        'tanggal_selesai' => now(),
                        'services'        => $data['services'],
                        'total_biaya'     => $total,
                        'log_status'      => $riwayatLengkap,
                        'user_id'         => auth()->id(),
                    ]);

                    $record->delete();
                    return $newEntry;
                });

                // 4. CEK KONDISI: Apakah admin memilih untuk mengirim WA?
                $pilihanWa = $data['kirim_wa_via'] ?? 'none';
                $nomorWA = $jadi->dataClient?->nomor_wa;

                if ($pilihanWa !== 'none' && $nomorWA) {
                    // Bersihkan format nomor WA
                    $nomorWA = preg_replace('/[^0-9]/', '', $nomorWA);
                    if (str_starts_with($nomorWA, '0')) {
                        $nomorWA = '62' . substr($nomorWA, 1);
                    }

                    $namaPelanggan = $jadi->nama_pelanggan ?? 'Pelanggan';
                    $linkTracking = url("/tracking/{$jadi->token}");

                    // 🚀 1. Ambil dan susun rincian biaya dari item kerusakan/layanan
                    $teksRincian = "";
                    // Sesuaikan 'services' atau 'rincian_kerusakan' dengan nama relasi di model kamu
                    $items = $jadi->services ?? [];

                    foreach ($items as $index => $item) {
                        // 🚀 PERBAIKAN: Sesuaikan dengan nama field di Repeater yaitu 'service' dan 'biaya'
                        $namaLayanan = $item['service'] ?? $item->service ?? 'Layanan/Part';
                        $biayaItem = $item['biaya'] ?? $item->biaya ?? 0;

                        // Susun baris teks rincian
                        $teksRincian .= "- " . $namaLayanan . ": Rp " . number_format((int)$biayaItem, 0, ',', '.') . "\n";
                    }

                    // 🚀 2. Ambil potongan biaya jika ada (opsional, biar makin detail)
                    $potongan = $jadi->potongan_biaya ?? 0;
                    if ($potongan > 0) {
                        $teksRincian .= "- Potongan Biaya: -Rp " . number_format((int)$potongan, 0, ',', '.') . "\n";
                    }

                    // Susun Pesan WhatsApp
                    $pesan = "Asallamuallaikum {$namaPelanggan}\n\n" .
                        "Unit service Anda telah *SELESAI* dikerjakan.\n\n" .
                        "Unit: {$jadi->nama_barang}\n\n" .
                        "*Rincian Biaya Service:*\n" .
                        $teksRincian . "\n" . // 🚀 Menyisipkan rincian di sini
                        "*Total Biaya:* Rp " . number_format($total, 0, ',', '.') . "\n\n" .
                        "Silakan ambil unit Anda dengan menunjukkan *QR CODE* pengambilan pada link berikut:\n" .
                        "{$linkTracking}\n\n" .
                        "Hormat kami,\nAcegroup Service Center";

                    // Tentukan Token Fonnte berdasarkan tombol toggle yang diklik
                    $tokenFonnte = ($pilihanWa === 'admin2')
                        ? config('services.fonnte.admin2')
                        : config('services.fonnte.admin1');

                    // Eksekusi Tembak API Fonnte murni di background (Pola Pelayanan.php)
                    try {
                        $response = \Illuminate\Support\Facades\Http::withHeaders([
                            'Authorization' => $tokenFonnte,
                        ])->timeout(10)->post('https://api.fonnte.com/send', [
                            'target'      => $nomorWA,
                            'message'     => $pesan,
                            'countryCode' => '62',
                        ]);

                        if ($response->json('status') === true) {
                            \Filament\Notifications\Notification::make()
                                ->title('Unit Selesai & WA Terkirim')
                                ->body('Data berhasil dipindahkan ke rak "Jadi". Nota terkirim otomatis via ' . ($pilihanWa === 'admin2' ? 'Admin 2' : 'Admin 1'))
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Data Dipindahkan, WA Gagal')
                                ->body('Fonnte gagal memproses kiriman. Silakan cek kuota atau device.')
                                ->warning()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Data Dipindahkan, API Error')
                            ->body('Gagal menghubungi server API Fonnte dari background.')
                            ->danger()
                            ->send();
                    }
                } else {
                    // Jika memilih 'none' atau nomor WA kosong, tampilkan notifikasi sukses database biasa
                    \Filament\Notifications\Notification::make()
                        ->title('Unit Berhasil Diselesaikan')
                        ->body('Data telah berhasil dipindahkan ke rak "Jadi" tanpa pengiriman WhatsApp.')
                        ->success()
                        ->send();
                }

                // 6. Selesaikan Action agar Livewire merefresh tabel
                $action->success();
            })
            ->slideOver()
            ->modalWidth('full')
            ->button();
    }
}
