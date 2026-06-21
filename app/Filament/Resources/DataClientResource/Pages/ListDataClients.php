<?php

namespace App\Filament\Resources\DataClientResource\Pages;

use App\Filament\Resources\DataClientResource;
use App\Models\Device;
use App\Services\FcmService;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Http;

class ListDataClients extends ListRecords
{
    protected static string $resource = DataClientResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //
        ];
    }
}
