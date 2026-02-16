<?php

namespace App\Filament\App\Resources\ServiceMasukResource\Pages;

use App\Filament\App\Resources\ServiceMasukResource;
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
