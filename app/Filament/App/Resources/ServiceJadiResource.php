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

                                //Tables\Columns\TextColumn::make('nomor_surat')->searchable(),

                                Tables\Columns\TextColumn::make('tanggal_selesai')
                                    ->badge()
                                    ->date(),

                                Tables\Columns\TextColumn::make('barang')
                                    ->label('Barang')
                                    ->alignLeft()
                                    //->badge()
                                    ->html()
                                    ->getStateUsing(
                                        fn($record) =>
                                        fn($record) =>
                                        '<strong style="font-size: 1.25rem;">' . e($record->category->category) . ' ' . e($record->nama_barang) . '</strong>'

                                    )
                                    ->searchable(),

                                Tables\Columns\TextColumn::make('dataClient.nama')
                                    ->label('Nama Client')
                                    ->searchable(),

                            ])->space(1),

                            // Bagian kanan (tanggal + QR)
                            Stack::make([

                                Tables\Columns\TextColumn::make('total_biaya')
                                    ->money('IDR', locale: 'id')
                                    ->alignRight(),

                                Tables\Columns\ViewColumn::make('qrcode')
                                    ->label('QR')
                                    ->view('filament.tables.qrcode')
                                    ->alignRight(),
                                //->tooltip(fn($record) => $record->nomor_surat)

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
                Tables\Actions\EditAction::make()->button(),
                Tables\Actions\DeleteAction::make()
                    ->button()
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
