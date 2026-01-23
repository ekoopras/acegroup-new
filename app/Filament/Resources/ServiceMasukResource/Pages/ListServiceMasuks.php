<?php

namespace App\Filament\Resources\ServiceMasukResource\Pages;

use App\Filament\Resources\ServiceMasukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceMasuks extends ListRecords
{
    protected static string $resource = ServiceMasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
