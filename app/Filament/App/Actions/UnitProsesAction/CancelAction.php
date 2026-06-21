<?php

namespace App\Filament\App\Actions\UnitProsesAction;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\DB;

class CancelAction
{
    public static function make(): Action
    {
        return Action::make('cancel')
            ->label('')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->mountUsing(function (\Filament\Forms\Form $form) {
                // Set default toggle ke none saat modal dibuka pertama kali
                $form->fill([
                    'kirim_wa_via' => 'none',
                ]);
            })
            ->form([
                Select::make('alasan_batal')
                    ->label('Alasan Pembatalan')
                    ->options([
                        'Part Kosong' => 'Part Kosong / Tidak Tersedia',
                        'User Tidak Deal' => 'User Tidak Setuju Biaya (Cancel User)',
                        'Tidak Bisa Diperbaiki' => 'Kondisi Unit Tidak Memungkinkan (Gagal)',
                        'Resiko Terlalu Tinggi' => 'Resiko Terlalu Tinggi',
                    ])
                    ->required(),

                // 🛠️ TAMBAHAN INTEGRASI: Opsi Pengiriman Fonnte via ToggleButtons
                \Filament\Forms\Components\ToggleButtons::make('kirim_wa_via')
                    ->label('Opsi Pengiriman Notifikasi / Nota Cancel WhatsApp:')
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

                Textarea::make('catatan_tambahan')
                    ->label('Catatan Teknisi')
                    ->placeholder('Jelaskan alasan teknis di sini...')
                    ->rows(3),

                TextInput::make('total_biaya')
                    ->label('Total Biaya')
                    ->default(0)
                    ->prefix('Rp')
                    ->disabled()
                    ->dehydrated(true),
            ])
            ->action(function ($record, array $data, \Filament\Tables\Actions\Action $action) {
                // 1. Ambil riwayat log lama & pastikan ter-decode jika bentuknya string
                $riwayatLengkap = $record->log_status ?? [];
                if (is_string($riwayatLengkap)) {
                    $riwayatLengkap = json_decode($riwayatLengkap, true);
                }
                if (!is_array($riwayatLengkap)) {
                    $riwayatLengkap = [];
                }

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
                        'nama_pelanggan'  => $record->nama_pelanggan,
                        'nama_barang'     => $record->nama_barang,
                        'nomor_surat'     => $record->nomor_surat,
                        'qrcode'          => $record->qrcode,
                        'token'           => $record->token,
                        'tanggal_masuk'   => $record->tanggal_masuk,
                        'tanggal_selesai' => now(),
                        'garansi'         => 'None',
                        'services'        => [['service' => 'Pembatalan: ' . $data['alasan_batal'], 'biaya' => 0]],
                        'total_biaya'     => 0,
                        'log_status'      => $riwayatLengkap,
                        'user_id'         => auth()->id(),
                    ]);

                    $record->delete(); // Hapus dari table proses
                    return $newEntry;
                });

                // 3. CEK KONDISI: Apakah admin memilih untuk mengirim WA?
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

                    // Susun Pesan WhatsApp Khusus Pembatalan
                    $pesan = "Asallamuallaikum {$namaPelanggan}\n\n" .
                        "Kami menginfokan bahwa unit service Anda *DIBATALKAN/GAGAL*.\n\n" .
                        "Unit: {$jadi->nama_barang}\n" .
                        "Alasan: {$data['alasan_batal']}\n" .
                        "Biaya: *FREE (Rp 0)*\n\n" .
                        "Silakan ambil unit Anda kembali dengan menunjukkan *QR CODE* pengambilan pada link berikut:\n" .
                        "{$linkTracking}\n\n" .
                        "Hormat kami,\nAcegroup Service Center";

                    // Tentukan Token Fonnte berdasarkan tombol toggle yang diklik
                    $tokenFonnte = ($pilihanWa === 'admin2')
                        ? config('services.fonnte.admin2')
                        : config('services.fonnte.admin1');

                    // Eksekusi Tembak API Fonnte di background (Pola Pelayanan.php murni)
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
                                ->title('Unit Dibatalkan & WA Terkirim')
                                ->body('Data dipindahkan ke rak "Jadi". Nota cancel terkirim via ' . ($pilihanWa === 'admin2' ? 'Admin 2' : 'Admin 1'))
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title('Data Dipindahkan, WA Gagal')
                                ->body('Fonnte gagal memproses kiriman. Silakan cek kuota atau status device.')
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
                    // Jika memilih 'none' atau nomor WA kosong, tampilkan notifikasi sukses biasa
                    \Filament\Notifications\Notification::make()
                        ->title('Unit Dibatalkan')
                        ->body('Data telah berhasil dipindahkan ke rak "Jadi" dengan biaya Rp 0 tanpa WhatsApp.')
                        ->success()
                        ->send();
                }

                // 4. Trigger kesuksesan komponen Livewire sebelum data baris terhapus sepenuhnya
                $action->success();
            })
            ->slideOver()
            ->modalWidth('full')
            ->button();
    }
}
