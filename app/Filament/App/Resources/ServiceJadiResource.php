<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ServiceJadiResource\Pages;
use App\Filament\App\Resources\ServiceJadiResource\RelationManagers;
use App\Models\LogService;
use App\Models\ServiceJadi;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceJadiResource extends Resource
{
    protected static ?string $model = ServiceJadi::class;

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';


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

                Grid::make([
                    'default' => 1, // 1 kolom di mobile
                    'md' => 2,      // 2 kolom di tablet / desktop
                ])
                    ->schema([

                        Split::make([

                            // Bagian kiri (informasi utama)
                            Stack::make([
                                Tables\Columns\TextColumn::make('nomor_surat')
                                    ->badge()
                                    ->searchable(),
                                Tables\Columns\TextColumn::make('dataClient.nama')
                                    ->label('Nama Client')
                                    ->alignLeft()
                                    ->searchable(),

                                Tables\Columns\TextColumn::make('category.category')
                                    ->alignLeft()
                                    ->searchable(),

                                Tables\Columns\TextColumn::make('nama_barang')
                                    ->alignLeft()
                                    ->searchable(),

                            ])->space(1),

                            // Bagian kanan (tanggal + QR)
                            Stack::make([

                                Tables\Columns\TextColumn::make('tanggal_selesai')
                                    ->badge()
                                    ->alignRight()
                                    ->date(),

                                Tables\Columns\TextColumn::make('total_biaya')
                                    ->money('IDR', locale: 'id')
                                    ->badge()
                                    ->color('success')
                                    ->alignRight(),

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
                                    ->separator(',')
                                    ->alignRight(),

                            ])->space(1),

                        ])
                            ->extraAttributes([
                                'class' => 'py-4 border-b border-gray-200 dark:border-gray-800'
                            ]),

                    ])

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('sudah_diambil')
                    ->label('Sudah Diambil')
                    ->button(),
                Tables\Actions\Action::make('chat_konfirmasi')
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
                    ->openUrlInNewTab()
                    ->button(),

            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListServiceJadis::route('/'),
            'create' => Pages\CreateServiceJadi::route('/create'),
            'edit' => Pages\EditServiceJadi::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->isSuperAdmin()) {
            return $query; // super admin lihat semua
        }

        //return $query->where('mapel_id', auth()->user()->mapel_id); // guru hanya mapel sendiri

        return $query->whereIn(
            'category_id',
            auth()->user()->category()->pluck('categories.id')
        );
    }
}
