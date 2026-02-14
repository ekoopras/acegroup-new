<?php

namespace App\Filament\Resources\DataClientResource\Pages;

use App\Filament\Resources\DataClientResource;
use App\Models\Device;
use App\Services\FcmService;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Http;

class CreateDataClient extends CreateRecord
{
    protected static string $resource = DataClientResource::class;
}
