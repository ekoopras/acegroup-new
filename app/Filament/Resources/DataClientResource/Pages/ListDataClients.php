<?php

namespace App\Filament\Resources\DataClientResource\Pages;

use App\Filament\Resources\DataClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataClients extends ListRecords
{
    protected static string $resource = DataClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
