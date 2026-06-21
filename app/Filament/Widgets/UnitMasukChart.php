<?php

namespace App\Filament\Widgets;

use App\Models\ServiceMasuk;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class UnitMasukChart extends ChartWidget
{
    protected static ?string $heading = 'Statistik Unit Service Masuk Per Hari';


    protected function getData(): array
    {
        // 1. Tentukan rentang waktu 30 hari ke belakang sampai hari ini
        $startDate = Carbon::now()->subDays(30)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        // 2. Ambil data jumlah unit masuk per tanggal langsung dari Database
        $rawData = ServiceMasuk::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('COUNT(*) as total')
        )
            ->whereBetween('created_at', [$startDate, $endDate])
            ->groupBy('date')
            ->orderBy('date', 'ASC')
            ->pluck('total', 'date')
            ->toArray();

        // 3. Lakukan looping tanggal agar hari yang kosong (0 unit masuk) tetap muncul di grafik
        $dataGraph = [];
        $labels = [];

        for ($date = $startDate->clone(); $date->lte($endDate); $date->addDay()) {
            $formattedDate = $date->format('Y-m-d');
            $labelDate = $date->format('d M'); // Contoh hasil format label: "21 Jun"

            $labels[] = $labelDate;
            // Jika di tanggal tersebut tidak ada data, otomatis diisi angka 0
            $dataGraph[] = $rawData[$formattedDate] ?? 0;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Unit Masuk',
                    'data' => $dataGraph,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)', // Efek warna transparan biru di bawah garis
                    'borderColor' => '#3b82f6', // Warna garis chart
                    'fill' => 'start',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
