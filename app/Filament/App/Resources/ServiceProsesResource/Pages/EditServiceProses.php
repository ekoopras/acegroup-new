<?php

namespace App\Filament\App\Resources\ServiceProsesResource\Pages;

use App\Filament\App\Resources\ServiceProsesResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceProses extends EditRecord
{
    protected static string $resource = ServiceProsesResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\DeleteAction::make(),
        ];
    }

    public function getTitle(): string
    {
        return '';
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
