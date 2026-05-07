<?php

namespace App\Filament\Pages;

use App\Models\ServiceJadi;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Actions\Action;

class Pengambilan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static string $view = 'filament.pages.pengambilan';
    protected static ?string $navigationLabel = 'Kasir / Pengambilan';

    public $search = '';
    public $unit = null;

    // Fungsi untuk mencari unit setelah QR terscan
    public function findUnit($nomorSurat)
    {
        $this->unit = ServiceJadi::where('nomor_surat', $nomorSurat)->first();

        if (!$this->unit) {
            Notification::make()
                ->title('Unit tidak ditemukan atau sudah diambil')
                ->danger()
                ->send();
        }
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('bayar')
                ->label('Proses Pembayaran')
                ->color('success')
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Select::make('metode_pembayaran')
                        ->options([
                            'cash' => 'Tunai (Cash)',
                            'transfer' => 'Transfer Bank',
                        ])
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->prosesSelesai($data['metode_pembayaran']);
                })
                ->visible(fn() => $this->unit !== null),
        ];
    }

    public function prosesSelesai($metode)
    {
        // 1. Update log terakhir
        $logs = $this->unit->log_status ?? [];
        $logs[] = [
            'status' => 'Diambil',
            'tanggal' => now()->toDateTimeString(),
            'keterangan' => "Unit telah diambil. Pembayaran via: " . strtoupper($metode),
        ];

        // 2. Simpan ke tabel Log Service (atau update status di ServiceJadi)
        // Di sini kita asumsikan unit dipindah ke tabel 'Riwayat' atau tetap di ServiceJadi dengan flag 'diambil'
        $this->unit->update([
            'log_status' => $logs,
            'metode_bayar' => $metode,
            'tanggal_ambil' => now(),
        ]);

        Notification::make()->title('Transaksi Selesai!')->success()->send();

        $this->unit = null; // Reset tampilan
    }
}
