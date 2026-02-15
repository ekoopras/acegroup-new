<?php

namespace App\Filament\Resources\DataClientResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
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
                Forms\Components\TextInput::make('nama')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('nama')
            ->columns([
                TextColumn::make('barang')
                    ->label('Barang')
                    ->html()
                    ->getStateUsing(
                        fn($record) =>
                        '<strong>' . e($record->category->category) . '</strong><br>' .
                            e($record->nama_barang)
                    )
                    ->searchable(),

                TextColumn::make('tanggal_pengambilan')
                    ->date(),

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



                TextColumn::make('total_biaya')
                    ->money('IDR', locale: 'id'),

                TextColumn::make('garansi'),
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
                //Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
