<?php

namespace App\Filament\Widgets;

use App\Models\ServiceJadi;
use App\Models\ServiceMasuk;
use App\Models\ServiceProses;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ServiceOverview extends BaseWidget
{

    protected static ?int $sort = -4;

    protected function getStats(): array
    {
        // Jalankan raw query JSON untuk mengambil status terakhir dari array log_status
        $jsonQuery = 'JSON_UNQUOTE(JSON_EXTRACT(log_status, CONCAT("$[", JSON_LENGTH(log_status) - 1, "].status")))';
        return [
            // 1. Menghitung total data dari tabel servicemasuk
            Stat::make('Service Masuk', ServiceMasuk::count())
                ->label('')
                ->description('UNIT SERVICE MASUK')
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center [&>*]:text-center [&>*]:items-center [&>*]:justify-center'
                ]),

            // 2. Menghitung total data dari tabel serviceproses
            Stat::make('Dalam Proses', ServiceProses::count())
                ->label('')
                ->description('UNIT SERVICE PROSES')
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center'
                ]),

            // 3. Menghitung total data dari tabel servicejadi
            Stat::make('Service Jadi', ServiceJadi::count())
                ->label('')
                ->description('UNIT SERVICE JADI')
                ->extraAttributes([
                    'class' => 'text-center flex flex-col items-center justify-center'
                ]),

            Stat::make('Proses Cek', ServiceProses::whereRaw("$jsonQuery = ?", ['Proses Cek'])->count())
                ->label('')
                ->description('UNIT PROSES CEK')
                ->color('warning')
                ->extraAttributes([
                    'class' => 'p-2 text-center flex flex-col items-center justify-center [&>*]:text-center [&>*]:items-center [&>*]:justify-center text-xs'
                ]),

            // 3. Menghitung data "Pending" menggunakan raw query JSON
            Stat::make('Pending', ServiceProses::whereRaw("$jsonQuery = ?", ['Pending'])->count())
                ->label('')
                ->description('UNIT PENDING')
                ->color('danger')
                ->extraAttributes([
                    'class' => 'p-2 text-center flex flex-col items-center justify-center [&>*]:text-center [&>*]:items-center [&>*]:justify-center text-xs'
                ]),

            // 4. Menghitung data "Deal Kerjakan" menggunakan raw query JSON
            Stat::make('Deal Kerjakan', ServiceProses::whereRaw("$jsonQuery = ?", ['Deal Kerjakan'])->count())
                ->label('')
                ->description('UNIT DEAL KERJAKAN')
                ->color('success')
                ->extraAttributes([
                    'class' => 'p-2 text-center flex flex-col items-center justify-center [&>*]:text-center [&>*]:items-center [&>*]:justify-center text-xs'
                ]),
        ];
    }
}
