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

        $userCategories = auth()->user()->isSuperAdmin()
            ? null
            : auth()->user()->category()->pluck('categories.id');

        // Helper function untuk menghitung jumlah berdasarkan status terakhir + Role Divisi
        $getCount = function (string $status) use ($userCategories) {
            $query = \App\Models\ServiceProses::query();

            // Terapkan Filter Role Divisi yang sama dengan getEloquentQuery
            if ($userCategories !== null) {
                $query->whereIn('category_id', $userCategories);
            }

            return $query->whereRaw(
                'JSON_UNQUOTE(JSON_EXTRACT(log_status, CONCAT("$[", JSON_LENGTH(log_status) - 1, "].status"))) = ?',
                [$status]
            )->count();
        };

        return [
            'proses' => Tab::make('Proses')
                ->label('Proses Cek')
                ->query(fn($query) => $query->whereRaw(
                    'JSON_UNQUOTE(JSON_EXTRACT(log_status, CONCAT("$[", JSON_LENGTH(log_status) - 1, "].status"))) IN (?, ?)',
                    ['Proses Cek', 'Proses Pengerjaan']
                ))
                ->badge($getCount('Proses Cek') + $getCount('Proses Pengerjaan'))
                ->badgeColor('warning'),

            'pending' => Tab::make('Pending')
                ->label('Pending')
                ->query(fn($query) => $query->whereRaw(
                    'JSON_UNQUOTE(JSON_EXTRACT(log_status, CONCAT("$[", JSON_LENGTH(log_status) - 1, "].status"))) = ?',
                    ['Pending']
                ))
                ->badge($getCount('Pending'))
                ->badgeColor('danger'),

            'deal' => Tab::make('Deal')
                ->label('Deal Kerjakan')
                ->query(fn($query) => $query->whereRaw(
                    'JSON_UNQUOTE(JSON_EXTRACT(log_status, CONCAT("$[", JSON_LENGTH(log_status) - 1, "].status"))) = ?',
                    ['Deal Kerjakan']
                ))
                ->badge($getCount('Deal Kerjakan'))
                ->badgeColor('success'),

        ];
    }
}
