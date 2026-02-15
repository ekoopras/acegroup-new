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
        return [
            // 'all' => Tab::make('Semua')
            //     ->badge(ServiceProses::count()),

            'proses' => Tab::make('Proses')
                ->query(fn($query) => $query->where('status', 'Proses'))
                ->badge(ServiceProses::where('status', 'Proses')->count())
                ->badgeColor('warning'),

            'pending' => Tab::make('Pending')
                ->query(fn($query) => $query->where('status', 'Pending'))
                ->badge(ServiceProses::where('status', 'Pending')->count())
                ->badgeColor('danger'),

            'deal' => Tab::make('Deal')
                ->query(fn($query) => $query->where('status', 'Deal'))
                ->badge(ServiceProses::where('status', 'Deal')->count())
                ->badgeColor('success'),
        ];
    }
}
