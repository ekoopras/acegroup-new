<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LaporanTransaksiResource\Pages;
use App\Filament\Resources\LaporanTransaksiResource\RelationManagers;
use App\Models\LaporanTransaksi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LaporanTransaksiResource extends Resource
{
    protected static ?string $model = LaporanTransaksi::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'Data';
    protected static ?string $navigationLabel = 'Laporan Nota';


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
                TextColumn::make('tanggal')
                    ->label('Waktu Transaksi')
                    ->dateTime('d M Y H:i')
                    ->sortable(),

                TextColumn::make('nomor_surat')
                    ->label('Surat Terima')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('nomor_nota')
                    ->label('Nomor Nota')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('nama_pelanggan')
                    ->label('Nama pelanggan')
                    ->searchable()
                    ->copyable(),

                TextColumn::make('nama_barang')
                    ->label('Unit Barang')
                    ->description(fn($record) => "Kategori: " . ($record->category->category ?? '-'))
                    ->searchable(),

                TextColumn::make('teknisi')
                    ->label('Tim Teknisi')
                    ->badge()
                    ->separator(', ')
                    ->color('success'),

                TextColumn::make('total_bayar')
                    ->label('Total Pembayaran')
                    ->money('IDR')
                    ->sortable(),
                // ->summarize(
                //     \Filament\Tables\Columns\Summarizers\Sum::make()
                //         ->label('Total Omzet')
                //         ->money('IDR')
                // ),

                TextColumn::make('metode_bayar')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'success',
                        'transfer' => 'info',
                        default => 'gray',
                    }),
            ])
            ->filters([
                //
            ])
            ->actions([
                //Tables\Actions\ViewAction::make(),
                //Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     //Tables\Actions\DeleteBulkAction::make(),
                // ]),
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
            'index' => Pages\ListLaporanTransaksis::route('/'),
            //'create' => Pages\CreateLaporanTransaksi::route('/create'),
            //'view' => Pages\ViewLaporanTransaksi::route('/{record}'),
            //'edit' => Pages\EditLaporanTransaksi::route('/{record}/edit'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()->isSuperAdmin(); // hanya super admin
    }
}
