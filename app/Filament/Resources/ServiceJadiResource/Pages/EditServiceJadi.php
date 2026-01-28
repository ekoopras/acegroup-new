<?php

namespace App\Filament\Resources\ServiceJadiResource\Pages;

use App\Filament\Resources\ServiceJadiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceJadi extends EditRecord
{
    protected static string $resource = ServiceJadiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
