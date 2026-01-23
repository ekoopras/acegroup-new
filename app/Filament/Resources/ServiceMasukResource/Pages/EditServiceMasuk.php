<?php

namespace App\Filament\Resources\ServiceMasukResource\Pages;

use App\Filament\Resources\ServiceMasukResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceMasuk extends EditRecord
{
    protected static string $resource = ServiceMasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
