<?php

namespace App\Filament\Resources\DataServiceResource\Pages;

use App\Filament\Resources\DataServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataServices extends ListRecords
{
    protected static string $resource = DataServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
            Actions\Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-m-arrow-path') // Icon panah berputar
                ->color('success')
                ->action(fn() => null),
        ];
    }
}
