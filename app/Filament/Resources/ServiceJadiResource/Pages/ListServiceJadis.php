<?php

namespace App\Filament\Resources\ServiceJadiResource\Pages;

use App\Filament\Resources\ServiceJadiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceJadis extends ListRecords
{
    protected static string $resource = ServiceJadiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
