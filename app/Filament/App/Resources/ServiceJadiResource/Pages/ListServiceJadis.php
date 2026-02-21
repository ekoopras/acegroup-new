<?php

namespace App\Filament\App\Resources\ServiceJadiResource\Pages;

use App\Filament\App\Resources\ServiceJadiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceJadis extends ListRecords
{
    protected static string $resource = ServiceJadiResource::class;

    public function getTitle(): string
    {
        return '';
    }

    // protected function getHeaderActions(): array
    // {
    //     return [
    //         Actions\CreateAction::make(),
    //     ];
    // }
}
