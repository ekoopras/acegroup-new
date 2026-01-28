<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceJadiResource\Pages;
use App\Filament\Resources\ServiceJadiResource\RelationManagers;
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

                Tables\Columns\TextColumn::make('nama_client'),

                Tables\Columns\TextColumn::make('biaya')
                    ->money('IDR', true),

                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->date(),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
