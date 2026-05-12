<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceJadiResource\Pages;
use App\Filament\Resources\ServiceJadiResource\RelationManagers;
use App\Models\LogService;
use App\Models\ServiceJadi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceJadiResource extends Resource
{
    protected static ?string $model = ServiceJadi::class;
    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nomor_surat')->searchable(),

                Tables\Columns\TextColumn::make('nama_barang'),

                Tables\Columns\TextColumn::make('dataClient.nama')
                    ->label('Nama Client')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_biaya')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->date(),

                Tables\Columns\TextColumn::make('teknisi_tim')
                    ->label('Teknisi Terlibat')
                    ->badge()
                    ->color('info')
                    ->getStateUsing(function ($record) {
                        $logs = $record->log_status;

                        if (is_array($logs) && !empty($logs)) {
                            // 1. Ambil semua teknisi_id dari tiap baris log
                            $userIds = collect($logs)
                                ->pluck('teknisi_id')
                                ->filter()
                                ->unique();

                            // 2. Ambil nama user berdasarkan ID tersebut
                            return \App\Models\User::whereIn('id', $userIds)
                                ->pluck('name')
                                ->toArray();
                        }

                        return null;
                    })
                    ->separator(','), // Memisahkan array menjadi badge-badge terpisah

                // Tables\Columns\ViewColumn::make('qrcode')
                //     ->label('QR')
                //     ->view('filament.tables.qrcode')
                //     //->tooltip(fn($record) => $record->nomor_surat)
                //     ->alignCenter(),


            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('chat_konfirmasi')
                    ->label('Kabarin Dia')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(function ($record) {
                        // 1. Ambil data dari record
                        $namaPelanggan = $record->dataClient->nama ?? 'Pelanggan';
                        $nomorWa = $record->dataClient->nomor_wa ?? '';
                        $total = $record->total_biaya ?? 0; // Pastikan nama kolom biaya sesuai
                        $linkTracking = url("/tracking/{$record->token}"); // Sesuaikan route tracking kamu

                        // 2. Format Nomor WA
                        $formattedNumber = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $nomorWa));

                        // 3. Susun Pesan sesuai format kemarin
                        $pesan = "Asallamuallaikum {$namaPelanggan}\n\n" .
                            "Unit service Anda telah *SELESAI* dikerjakan.\n\n" .
                            "Unit: {$record->nama_barang}\n" .
                            "Total Biaya: Rp " . number_format($total, 0, ',', '.') . "\n" .
                            "Garansi: " . ($record->garansi == 'None' || !$record->garansi ? 'Tanpa Garansi' : $record->garansi) . "\n\n" .
                            "Silakan ambil unit Anda dengan menunjukkan *QR CODE* pengambilan pada link berikut:\n" .
                            "{$linkTracking}\n\n" .
                            "Hormat kami,\nAcegroup Service Center";

                        // 4. Return URL
                        return "https://api.whatsapp.com/send?phone={$formattedNumber}&text=" . urlencode($pesan);
                    })
                    ->openUrlInNewTab()->button(),
                //Tables\Actions\EditAction::make()->button(),
                // Tables\Actions\DeleteAction::make()
                //     ->action(function ($record) {

                //         LogService::create([
                //             'category_id'         => $record->category_id,
                //             'data_client_id'      => $record->data_client_id,
                //             'nama_barang'         => $record->nama_barang,
                //             'tanggal_pengambilan' => now(),
                //             'services'            => $record->services,
                //             'total_biaya'         => $record->total_biaya,
                //             'garansi'             => $record->garansi,
                //         ]);

                //         $record->delete();
                //     })
                //     ->requiresConfirmation()
                //     ->button(),

            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServiceJadis::route('/'),
            'create' => Pages\CreateServiceJadi::route('/create'),
            'edit' => Pages\EditServiceJadi::route('/{record}/edit'),
        ];
    }
}
