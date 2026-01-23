<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceMasukResource\Pages;
use App\Filament\Resources\ServiceMasukResource\RelationManagers;
use App\Models\ServiceMasuk;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceMasukResource extends Resource
{
    protected static ?string $model = ServiceMasuk::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Service Masuk';
    protected static ?string $pluralLabel = 'Service Masuk';
    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('category_id')
                    ->label('Kategori')
                    ->relationship('category', 'category')
                    ->searchable()
                    ->required(),

                Forms\Components\TextInput::make('nama_client')
                    ->label('Nama Client')
                    ->required(),

                Forms\Components\TextInput::make('nomor_wa')
                    ->label('Nomor WhatsApp')
                    ->tel()
                    ->required()
                    ->helperText('Gunakan format 628xxxx'),

                Forms\Components\DatePicker::make('tanggal_masuk')
                    ->label('Tanggal Masuk')
                    ->default(now())
                    ->required(),

                Forms\Components\Textarea::make('kerusakan')
                    ->label('Kerusakan')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\CheckboxList::make('perlengkapan')
                    ->label('Perlengkapan')
                    ->options([
                        'tas' => 'Tas',
                        'adaptor_charger' => 'Adaptor Charger',
                        'kabel_power' => 'Kabel Power',
                        'kabel_usb_print' => 'Kabel USB Print',
                        'kardus' => 'Kardus',
                        'battrai' => 'Battrai',
                        'kesing_kanan' => 'Kesing Kanan',
                        'kesing_kiri' => 'Kesing Kiri',
                        'usb_data' => 'USB Data',
                    ])
                    ->columns(2),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan Tambahan')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tanggal_masuk')
                    ->label('Masuk')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('category.category')
                    ->label('Kategori')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama_client')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nomor_wa')
                    ->label('WhatsApp')
                    ->url(fn($record) => 'https://wa.me/' . $record->nomor_wa, true)
                    ->openUrlInNewTab(),

                Tables\Columns\TextColumn::make('perlengkapan')
                    ->badge()
                    ->separator(',')
                    ->limit(3),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])->icon('heroicon-o-ellipsis-vertical'),
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
            'index' => Pages\ListServiceMasuks::route('/'),
            //'create' => Pages\CreateServiceMasuk::route('/create'),
            //'edit' => Pages\EditServiceMasuk::route('/{record}/edit'),
        ];
    }
}
