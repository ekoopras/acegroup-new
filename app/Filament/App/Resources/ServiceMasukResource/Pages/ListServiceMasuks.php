<?php

namespace App\Filament\App\Resources\ServiceMasukResource\Pages;

use App\Filament\App\Resources\ServiceMasukResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceMasuks extends ListRecords
{
    protected static string $resource = ServiceMasukResource::class;

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
