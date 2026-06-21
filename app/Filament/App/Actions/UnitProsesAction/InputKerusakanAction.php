<?php

namespace App\Filament\App\Actions\UnitProsesAction;

use Filament\Tables\Actions\Action;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Http;

class InputKerusakanAction
{
    public static function make(): Action
    {
        return Action::make('inputKerusakan')
            ->label('')
            ->icon('heroicon-m-wrench')
            ->button()
            ->slideOver()
            ->modalWidth('full')
            ->modalHeading('Input Rincian Kerusakan & Estimasi Biaya')
            ->modalSubmitActionLabel('Simpan Data')
            ->form([
                // 1. Pilihan Status Manual via ToggleButtons
                ToggleButtons::make('status_pilihan')
                    ->label('Pilih Status Unit Saat Ini:')
                    ->options([
                        'Proses Cek' => 'Proses Cek',
                        'Pending' => 'Pending',
                        'Deal Kerjakan' => 'Deal Kerjakan',
                    ])
                    ->colors([
                        'Proses Cek' => 'warning',
                        'Pending' => 'danger',
                        'Deal Kerjakan' => 'success',
                    ])
                    ->icons([
                        'Proses Cek' => 'heroicon-m-magnifying-glass',
                        'Pending' => 'heroicon-m-clock',
                        'Deal Kerjakan' => 'heroicon-m-wrench-screwdriver',
                    ])
                    ->required()
                    ->inline()
                    ->afterStateHydrated(function ($component, $record) {
                        $logs = $record->log_status;
                        if (is_string($logs)) {
                            $logs = json_decode($logs, true);
                        }
                        if (!empty($logs) && is_array($logs)) {
                            $lastLog = end($logs);
                            $component->state($lastLog['status'] ?? 'Pending');
                        } else {
                            $component->state('Pending');
                        }
                    }),

                // 2. Opsi Pengiriman Fonnte via ToggleButtons
                ToggleButtons::make('kirim_wa_via')
                    ->label('Opsi Pengiriman Tanda Terima / Nota WhatsApp:')
                    ->options([
                        'none' => '🚫 Jangan Kirim',
                        'admin1' => 'Admin 1',
                        'admin2' => 'Admin 2',
                    ])
                    ->colors([
                        'none' => 'danger',
                        'admin1' => 'success',
                        'admin2' => 'info',
                    ])
                    ->icons([
                        'none' => 'heroicon-m-x-circle',
                        'admin1' => 'heroicon-m-paper-airplane',
                        'admin2' => 'heroicon-m-paper-airplane',
                    ])
                    ->default('none')
                    ->inline()
                    ->required(),

                // 3. Repeater Rincian Kerusakan
                Repeater::make('rincian_kerusakan')
                    ->label('Rincian Kerusakan & Biaya Baru')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextInput::make('kerusakan')
                                    ->label('Nama Kerusakan / Part')
                                    ->placeholder('Contoh: Ganti LCD Original')
                                    ->required(),

                                TextInput::make('biaya')
                                    ->label('Biaya (Rp)')
                                    ->placeholder('Contoh: 350.000')
                                    ->numeric()
                                    ->required(),

                                TextInput::make('garansi')
                                    ->label('Garansi')
                                    ->placeholder('1 Bulan'),

                                TextInput::make('kode')
                                    ->label('Kode / Serial Part')
                                    ->placeholder('Contoh: LCD-IP11-01'),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->defaultItems(1)
                    ->createItemButtonLabel('Tambah Kerusakan/Part Baru')
                    ->afterStateHydrated(function ($component, $record) {
                        $logs = $record->log_status;

                        if (is_string($logs)) {
                            $logs = json_decode($logs, true);
                        }

                        if (is_array($logs) && !empty($logs)) {
                            $reversedLogs = array_reverse($logs);

                            foreach ($reversedLogs as $log) {
                                if (isset($log['rincian_biaya']) && is_array($log['rincian_biaya'])) {
                                    $component->state($log['rincian_biaya']);
                                    return;
                                }
                            }
                        }
                        $component->state([]);
                    })
                    ->statePath('rincian_kerusakan'),
            ])
            ->action(function ($record, array $data, Action $action) {
                // 1. Ambil riwayat log lama & tambahkan rincian baru
                $currentLogs = $record->log_status ?? [];
                if (is_string($currentLogs)) {
                    $currentLogs = json_decode($currentLogs, true);
                }
                if (!is_array($currentLogs)) {
                    $currentLogs = [];
                }

                $statusTerpilih = $data['status_pilihan'];

                $currentLogs[] = [
                    'status' => $statusTerpilih,
                    'tanggal' => now()->toDateTimeString(),
                    'keterangan' => 'Rincian kerusakan diperbarui. Status diset manual ke ' . $statusTerpilih . '.',
                    'user_id' => auth()->id(),
                    'rincian_biaya' => $data['rincian_kerusakan'],
                ];

                // 2. Update data ke database
                $record->update([
                    'log_status' => $currentLogs
                ]);

                // 3. Cek pengiriman WhatsApp
                $pilihanWa = $data['kirim_wa_via'] ?? 'none';
                $nomorWa = $record->dataClient?->nomor_wa;

                if ($pilihanWa !== 'none' && $nomorWa) {
                    $nomorWa = preg_replace('/[^0-9]/', '', $nomorWa);
                    if (str_starts_with($nomorWa, '0')) {
                        $nomorWa = '62' . substr($nomorWa, 1);
                    }

                    $rincian = $data['rincian_kerusakan'] ?? [];

                    $pesan = "*NOTA ESTIMASI PERBAIKAN*\n";
                    $pesan .= "Halo Kak *" . ($record->nama_pelanggan ?? 'Pelanggan') . "*,\n";
                    $pesan .= "Berikut rincian kerusakan dan biaya untuk unit *" . ($record->nama_barang ?? '') . "*:\n\n";

                    $totalBiaya = 0;
                    foreach ($rincian as $index => $item) {
                        $no = $index + 1;
                        $biayaFormat = number_format($item['biaya'] ?? 0, 0, ',', '.');
                        $pesan .= "{$no}. *{$item['kerusakan']}*\n";
                        $pesan .= "    Biaya: Rp {$biayaFormat}\n";
                        $pesan .= "    Garansi: " . ($item['garansi'] ?: '-') . "\n\n";
                        $totalBiaya += (int)($item['biaya'] ?? 0);
                    }

                    $pesan .= "-----------------------------------\n";
                    $pesan .= "*Total Estimasi:* Rp " . number_format($totalBiaya, 0, ',', '.') . "\n\n";
                    $pesan .= "Status Saat Ini: *{$statusTerpilih}*\n";
                    $pesan .= "Mohon konfirmasinya apakah disetujui untuk dikerjakan? Terima kasih. 🙏";

                    $tokenFonnte = ($pilihanWa === 'admin2')
                        ? config('services.fonnte.admin2')
                        : config('services.fonnte.admin1');

                    try {
                        $response = Http::withHeaders([
                            'Authorization' => $tokenFonnte,
                        ])->timeout(10)->post('https://api.fonnte.com/send', [
                            'target'      => $nomorWa,
                            'message'     => $pesan,
                            'countryCode' => '62',
                        ]);

                        if ($response->json('status') === true) {
                            Notification::make()
                                ->title('Data Tersimpan & WA Terkirim')
                                ->body('Nota estimasi berhasil dikirim via ' . ($pilihanWa === 'admin2' ? 'Admin 2' : 'Admin 1'))
                                ->success()->send();
                        } else {
                            Notification::make()
                                ->title('Data Tersimpan, WA Gagal')
                                ->body('Fonnte gagal memproses kiriman.')
                                ->warning()->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Data Tersimpan, API Error')
                            ->body('Gagal menghubungi server Fonnte di background.')
                            ->danger()->send();
                    }
                } else {
                    Notification::make()
                        ->title('Data Rincian Berhasil Disimpan')
                        ->body('Perubahan data unit telah berhasil disimpan ke database.')
                        ->success()->send();
                }

                $action->success();
            });
    }
}
