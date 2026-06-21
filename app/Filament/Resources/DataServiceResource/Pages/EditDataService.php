<?php

namespace App\Filament\Resources\DataServiceResource\Pages;

use App\Filament\Resources\DataServiceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDataService extends EditRecord
{
    protected static string $resource = DataServiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
