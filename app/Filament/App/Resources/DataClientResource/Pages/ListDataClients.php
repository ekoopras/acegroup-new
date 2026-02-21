<?php

namespace App\Filament\App\Resources\DataClientResource\Pages;

use App\Filament\App\Resources\DataClientResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDataClients extends ListRecords
{
    protected static string $resource = DataClientResource::class;

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
