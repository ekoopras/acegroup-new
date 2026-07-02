<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DataServiceResource\Pages;
use App\Filament\Resources\DataServiceResource\RelationManagers;
use App\Models\DataService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DataServiceResource extends Resource
{
    protected static ?string $model = DataService::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Data Service';

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

                Tables\Columns\TextColumn::make('nama_barang')
                    ->label('Barang')
                    // 🚀 TAMPILAN: Menggabungkan "Kategori" + "Nama Barang" (Contoh: Laptop Asus)
                    ->formatStateUsing(function ($state, $record) {
                        $kategori = $record->category->category ?? '';
                        return trim("{$kategori} {$state}");
                    })
                    // 🚀 SEARCH: Memaksa Filament agar bisa mencari berdasarkan kolom nama_barang DAN kategori sekaligus
                    ->searchable(query: function ($query, string $search) {
                        $query->where(function ($q) use ($search) {
                            $q->where('nama_barang', 'like', "%{$search}%")
                                ->orWhereHas('category', function ($subQuery) use ($search) {
                                    $subQuery->where('category', 'like', "%{$search}%");
                                });
                        });
                    }),

                Tables\Columns\TextColumn::make('nama_pelanggan')
                    ->label('Nama Pelanggan')
                    ->searchable(),

                Tables\Columns\TextColumn::make('dataClient.nomor_wa')
                    ->label('Whatsapp')
                    ->searchable(),

                Tables\Columns\TextColumn::make('kerusakan')
                    ->label('Daftar Kerusakan')
                    ->badge()
                    ->color('success')
                    ->listWithLineBreaks()
                    ->wrap(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->label('Tanggal Masuk'),

            ])
            ->defaultSort('created_at', 'desc')
            ->defaultPaginationPageOption(50) // Set default awal ke 10 data
            ->paginationPageOptions([50])
            ->filters([
                //
            ])
            ->actions([
                // Tables\Actions\EditAction::make()
                //     ->label('')
                //     ->color('success')
                //     ->button(),

                Tables\Actions\Action::make('print')
                    ->label('')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->button()
                    ->url(fn($record) => route('service.print.dataservice', $record->id))
                    ->openUrlInNewTab(),
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
            'index' => Pages\ListDataServices::route('/'),
            'create' => Pages\CreateDataService::route('/create'),
            'edit' => Pages\EditDataService::route('/{record}/edit'),
        ];
    }
}
