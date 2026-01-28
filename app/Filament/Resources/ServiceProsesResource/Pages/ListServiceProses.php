<?php

namespace App\Filament\Resources\ServiceProsesResource\Pages;

use App\Filament\Resources\ServiceProsesResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceProses extends ListRecords
{
    protected static string $resource = ServiceProsesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
