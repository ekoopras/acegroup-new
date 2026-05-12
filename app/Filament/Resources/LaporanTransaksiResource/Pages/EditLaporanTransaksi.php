<?php

namespace App\Filament\Resources\LaporanTransaksiResource\Pages;

use App\Filament\Resources\LaporanTransaksiResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLaporanTransaksi extends EditRecord
{
    protected static string $resource = LaporanTransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
