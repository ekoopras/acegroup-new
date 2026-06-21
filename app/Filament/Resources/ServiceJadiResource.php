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
    protected static ?string $navigationGroup = 'Data Service';

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

                Tables\Columns\TextColumn::make('dataClient.nama')
                    ->label('Nama Client')
                    ->searchable(),

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

                Tables\Columns\TextColumn::make('dataClient.nomor_wa')
                    ->label('whatsapp')
                    ->searchable(),

                Tables\Columns\TextColumn::make('total_biaya')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_masuk')
                    ->label('Unit Masuk')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('tanggal_selesai')
                    ->label('Unit Jadi')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->state(function ($record) {
                        $logs = $record->log_status;
                        if (is_string($logs)) {
                            $logs = json_decode($logs, true);
                        }
                        if (!empty($logs) && is_array($logs)) {
                            $lastLog = end($logs);
                            return $lastLog['status'] ?? 'Selesai';
                        }
                        return 'Selesai';
                    })
                    ->formatStateUsing(fn(string $state): string => trim($state))
                    ->icon(fn(string $state): string => match (strtolower(trim($state))) {

                        'selesai'     => 'heroicon-m-check-circle', // Ikon Sudah Jadi
                        'cancel / gagal'   => 'heroicon-m-x-circle',     // Ikon Gagal
                        default   => 'heroicon-m-information-circle',
                    })
                    // 🛠️ FIX WARNA: Diubah ke strtolower agar warna sinkron 100%
                    ->color(fn(string $state): string => match (strtolower(trim($state))) {

                        'selesai'     => 'success', // Hijau untuk Sukses Jadi
                        'cancel / gagal'   => 'danger',  // Merah untuk Cancel Gagal
                        default         => 'info',    // Biru jika tulisan di DB tidak kembar
                    }),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Teknisi')
                    ->badge()
                    ->color('success'),

            ])
            ->defaultPaginationPageOption(50) // Set default awal ke 10 data
            ->paginationPageOptions([50])
            ->filters([
                //
            ])
            ->actions([

                //

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
