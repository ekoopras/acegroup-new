<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataClientResource\Pages;
use App\Filament\Resources\DataClientResource\RelationManagers;
use App\Models\DataClient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DataClientResource extends Resource
{
    protected static ?string $model = DataClient::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Data Client';
    protected static ?string $pluralLabel = 'Data Client';
    protected static ?string $navigationGroup = 'Master Data';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama Client')
                    ->required()
                    ->maxLength(100),

                Forms\Components\TextInput::make('nomor_wa')
                    ->label('Nomor WhatsApp')
                    ->required()
                    ->tel()
                    ->helperText('Gunakan format 628xxxx'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('nama')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('nomor_wa')
                    ->label('WhatsApp')
                    ->url(fn($record) => 'https://wa.me/' . $record->nomor_wa, true)
                    ->openUrlInNewTab()
                    ->searchable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->date('d M Y'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->button(),
                Tables\Actions\DeleteAction::make()->button(),
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
            'index' => Pages\ListDataClients::route('/'),
            //'create' => Pages\CreateDataClient::route('/create'),
            //'edit' => Pages\EditDataClient::route('/{record}/edit'),
        ];
    }
}
