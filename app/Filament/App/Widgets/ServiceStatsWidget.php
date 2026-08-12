<?php

namespace App\Filament\App\Widgets;

use App\Models\ServiceMasuk;
use App\Models\ServiceProses;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class ServiceStatsWidget extends BaseWidget
{
    // Mengatur urutan agar tampil di bawah/di atas widget menu
    protected static ?int $sort = -1;

    // Tambahkan baris ini untuk mematikan auto-refresh/realtime sepenuhnya
    protected static ?string $pollingInterval = null;

    protected function getStats(): array
    {
        // 1. Count Total Unit Masuk (Antrian)
        $totalMasuk = ServiceMasuk::count();

        // 2. Count Unit Proses Khusus Teknisi / User yang Sedang Login
        $totalProsesSaya = ServiceProses::where('user_id', Auth::id())
            // Jika menggunakan relasi atau kolom nama teknisi, sesuaikan kolomnya (misal: 'teknisi_id')
            ->count();

        return [
            Stat::make('', $totalMasuk)
                ->description('Total unit dalam antrian')
                ->descriptionIcon('heroicon-m-inbox-arrow-down')
                ->color('primary')
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center',
                ]),

            Stat::make('', $totalProsesSaya)
                ->description('Pengerjaan aktif Anda')
                ->descriptionIcon('heroicon-m-cpu-chip')
                ->color('success')
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center',
                ]),
        ];
    }
}
