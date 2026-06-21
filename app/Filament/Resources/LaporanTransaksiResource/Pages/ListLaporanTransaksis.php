<?php

namespace App\Filament\Resources\LaporanTransaksiResource\Pages;

use App\Filament\Resources\LaporanTransaksiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLaporanTransaksis extends ListRecords
{
    protected static string $resource = LaporanTransaksiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
