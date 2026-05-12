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
        // Helper untuk badge count (UNQUOTE ditambahkan agar lebih aman)
        $getCount = function (string $status) {
            return \App\Models\ServiceProses::whereRaw(
                'JSON_UNQUOTE(JSON_EXTRACT(log_status, CONCAT("$[", JSON_LENGTH(log_status) - 1, "].status"))) = ?',
                [$status]
            )->count();
        };

        return [
            'proses' => Tab::make('Proses')
                ->query(fn($query) => $query->whereRaw(
                    'JSON_UNQUOTE(JSON_EXTRACT(log_status, CONCAT("$[", JSON_LENGTH(log_status) - 1, "].status"))) IN (?, ?)',
                    ['Proses Cek', 'Proses Pengerjaan']
                ))
                ->badge($getCount('Proses Cek') + $getCount('Proses Pengerjaan'))
                ->badgeColor('warning'),

            'pending' => Tab::make('Pending')
                ->query(fn($query) => $query->whereRaw(
                    'JSON_UNQUOTE(JSON_EXTRACT(log_status, CONCAT("$[", JSON_LENGTH(log_status) - 1, "].status"))) = ?',
                    ['Pending']
                ))
                ->badge($getCount('Pending'))
                ->badgeColor('danger'),

            'deal' => Tab::make('Deal')
                ->query(fn($query) => $query->whereRaw(
                    'JSON_UNQUOTE(JSON_EXTRACT(log_status, CONCAT("$[", JSON_LENGTH(log_status) - 1, "].status"))) = ?',
                    ['Deal']
                ))
                ->badge($getCount('Deal'))
                ->badgeColor('success'),

            'trial' => Tab::make('Trial')
                ->query(fn($query) => $query->whereRaw(
                    'JSON_UNQUOTE(JSON_EXTRACT(log_status, CONCAT("$[", JSON_LENGTH(log_status) - 1, "].status"))) = ?',
                    ['Trial']
                ))
                ->badge($getCount('Trial'))
                ->badgeColor('info'),
        ];
    }
}
