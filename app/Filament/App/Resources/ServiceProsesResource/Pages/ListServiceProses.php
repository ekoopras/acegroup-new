<?php

namespace App\Filament\App\Resources\ServiceProsesResource\Pages;

use App\Filament\App\Resources\ServiceProsesResource;
use App\Models\ServiceProses;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;

class ListServiceProses extends ListRecords
{
    protected static string $resource = ServiceProsesResource::class;

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }

    public function getTitle(): string
    {
        return '';
    }

    public function getTabs(): array
    {
        return [
            // 'all' => Tab::make('Semua')
            //     ->badge(ServiceProses::count()),

            // 'proses' => Tab::make('Proses')
            //     ->query(fn($query) => $query->where('status', 'Proses'))
            //     ->badge(ServiceProses::where('status', 'Proses')->count())
            //     ->badgeColor('warning'),

            // 'pending' => Tab::make('Pending')
            //     ->query(fn($query) => $query->where('status', 'Pending'))
            //     ->badge(ServiceProses::where('status', 'Pending')->count())
            //     ->badgeColor('danger'),

            // 'deal' => Tab::make('Deal')
            //     ->query(fn($query) => $query->where('status', 'Deal'))
            //     ->badge(ServiceProses::where('status', 'Deal')->count())
            //     ->badgeColor('success'),
            'proses' => Tab::make('Proses')
                ->query(fn($query) => $query->where('status', 'Proses'))
                ->badge(fn() => ServiceProsesResource::getEloquentQuery()
                    ->where('status', 'Proses')
                    ->count())
                ->badgeColor('warning'),

            'pending' => Tab::make('Pending')
                ->query(fn($query) => $query->where('status', 'Pending'))
                ->badge(fn() => ServiceProsesResource::getEloquentQuery()
                    ->where('status', 'Pending')
                    ->count())
                ->badgeColor('danger'),

            'deal' => Tab::make('Deal')
                ->query(fn($query) => $query->where('status', 'Deal'))
                ->badge(fn() => ServiceProsesResource::getEloquentQuery()
                    ->where('status', 'Deal')
                    ->count())
                ->badgeColor('success'),
        ];
    }
}
