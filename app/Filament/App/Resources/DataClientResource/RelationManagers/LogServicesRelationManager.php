<?php

namespace App\Filament\App\Resources\DataClientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class LogServicesRelationManager extends RelationManager
{
    protected static string $relationship = 'logServices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([


                Grid::make([
                    'default' => 1, // 1 kolom di mobile
                    'md' => 2,      // 2 kolom di tablet / desktop
                ])
                    ->schema([

                        Split::make([

                            // Bagian kiri (informasi utama)
                            Stack::make([

                                TextColumn::make('tanggal_pengambilan')
                                    ->badge()
                                    ->date(),

                                TextColumn::make('barang')
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


                                TextColumn::make('services')
                                    ->formatStateUsing(function ($state) {

                                        if (empty($state)) {
                                            return '-';
                                        }

                                        // Bungkus jadi JSON array valid
                                        $json = '[' . $state . ']';
                                        $data = json_decode($json, true);

                                        if (! is_array($data)) {
                                            return '-';
                                        }

                                        return collect($data)
                                            ->map(
                                                fn($item) =>
                                                $item['service'] . ' (Rp ' . number_format($item['biaya']) . ')'
                                            )
                                            ->implode('<br>');
                                    })
                                    ->html()   // WAJIB supaya <br> terbaca
                                    ->wrap(),





                            ])->space(1),

                            // Bagian kanan (tanggal + QR)
                            Stack::make([

                                TextColumn::make('total_biaya')
                                    ->alignRight()
                                    ->money('IDR', locale: 'id'),

                                TextColumn::make('garansi')
                                    ->alignRight(),

                            ])->space(1),

                        ]),

                    ])




            ])
            ->filters([
                //
            ])
            ->headerActions([
                //Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                //Tables\Actions\EditAction::make(),
                //Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
