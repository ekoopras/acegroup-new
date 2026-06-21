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
                TextColumn::make('nama_pelanggan')
                    ->label('Nama pelanggan')
                    ->searchable()
                    ->copyable(),

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

                TextColumn::make('tanggal')
                    ->label('Waktu Transaksi')
                    ->dateTime('d M Y')
                    ->sortable(),

                TextColumn::make('nomor_nota')
                    ->label('Nomor Nota')
                    ->searchable()
                    ->copyable(),


                TextColumn::make('total_bayar')
                    ->label('Total Pembayaran')
                    ->money('IDR', locale: 'id')
                    ->sortable(),

                TextColumn::make('metode_bayar')
                    ->label('Metode')
                    ->badge()
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->color(fn(string $state): string => match ($state) {
                        'cash' => 'success',
                        'transfer' => 'info',
                        default => 'gray',
                    }),

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
            ])
            ->defaultPaginationPageOption(50) // Set default awal ke 10 data
            ->paginationPageOptions([50])
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
