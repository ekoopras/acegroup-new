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
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceJadiResource extends Resource
{
    protected static ?string $model = ServiceJadi::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
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

                Tables\Columns\ViewColumn::make('qrcode')
                    ->label('QR')
                    ->view('filament.tables.qrcode')
                    //->tooltip(fn($record) => $record->nomor_surat)
                    ->alignCenter(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function ($record) {

                        LogService::create([
                            'category_id'         => $record->category_id,
                            'data_client_id'      => $record->data_client_id,
                            'nama_barang'         => $record->nama_barang,
                            'tanggal_pengambilan' => now(),
                            'services'            => $record->services,
                            'total_biaya'         => $record->total_biaya,
                            'garansi'             => $record->garansi,
                        ]);

                        $record->delete();
                    })
                    ->requiresConfirmation(),

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
