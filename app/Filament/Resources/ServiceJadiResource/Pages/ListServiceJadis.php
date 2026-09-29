<?php

namespace App\Filament\Resources\ServiceJadiResource\Pages;

use App\Filament\Resources\ServiceJadiResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceJadis extends ListRecords
{
    protected static string $resource = ServiceJadiResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),

            // Action Print Semua Data Service Jadi
            Actions\Action::make('print')
                ->label('Cetak Laporan')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->openUrlInNewTab()
                ->url(fn() => route('service-jadi.print')),
        ];
    }
}
