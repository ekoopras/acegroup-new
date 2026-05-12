<?php

namespace App\Filament\Resources\LaporanTransaksiResource\Pages;

use App\Filament\Resources\LaporanTransaksiResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLaporanTransaksi extends ViewRecord
{
    protected static string $resource = LaporanTransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
