<?php

namespace App\Filament\Resources\ServiceProsesResource\Pages;

use App\Filament\Resources\ServiceProsesResource;
use App\Models\ServiceProses;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListServiceProses extends ListRecords
{
    protected static string $resource = ServiceProsesResource::class;

    public function getTabs(): array
    {
        // Helper function untuk menghitung jumlah berdasarkan status terakhir
        $getCount = function (string $status) {
            return \App\Models\ServiceProses::whereRaw('JSON_EXTRACT(log_status, "$[last].status") = ?', [$status])->count();
        };

        return [

            'proses' => Tab::make('Proses')
                ->query(fn($query) => $query->whereRaw('JSON_EXTRACT(log_status, "$[last].status") IN (?, ?)', ['Proses Cek', 'Proses Pengerjaan']))
                ->badge($getCount('Proses Cek') + $getCount('Proses Pengerjaan'))
                ->badgeColor('warning'),

            'pending' => Tab::make('Pending')
                ->query(fn($query) => $query->whereRaw('JSON_EXTRACT(log_status, "$[last].status") = ?', ['Pending']))
                ->badge($getCount('Pending'))
                ->badgeColor('danger'),

            'deal' => Tab::make('Deal')
                ->query(fn($query) => $query->whereRaw('JSON_EXTRACT(log_status, "$[last].status") = ?', ['Deal']))
                ->badge($getCount('Deal'))
                ->badgeColor('success'),

            'selesai' => Tab::make('Selesai')
                ->query(fn($query) => $query->whereRaw('JSON_EXTRACT(log_status, "$[last].status") = ?', ['Selesai']))
                ->badge($getCount('Selesai'))
                ->badgeColor('info')
                ->label('Trial'),
        ];
    }
}
