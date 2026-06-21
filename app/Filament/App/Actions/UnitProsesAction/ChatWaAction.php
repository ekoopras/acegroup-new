<?php

namespace App\Filament\App\Actions\UnitProsesAction;

use Filament\Tables\Actions\Action;

class ChatWaAction
{
    public static function make(): Action
    {
        return Action::make('whatsapp')
            ->label('')
            ->button()
            ->icon('heroicon-o-chat-bubble-left-right')
            ->color('success')
            ->url(function ($record) {
                // 1. Ambil data dari relasi
                $client = $record->dataClient;
                $nomor_wa = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $client->nomor_wa));
                // 6. Return Link WhatsApp
                return "https://api.whatsapp.com/send?phone={$nomor_wa}&text=";
            })
            ->openUrlInNewTab();
    }
}
