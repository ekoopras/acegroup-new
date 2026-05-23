<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\DataClientResource\Pages;
use App\Filament\App\Resources\DataClientResource\RelationManagers;
use App\Models\DataClient;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\Layout\Grid;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DataClientResource extends Resource
{
    protected static ?string $model = DataClient::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Log Service';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nama')
                    ->label('Nama Client')
                    ->required()
                    ->disabled()
                    ->maxLength(100),

                Forms\Components\TextInput::make('nomor_wa')
                    ->label('Nomor WhatsApp')
                    ->required()
                    ->tel()
                    ->disabled()
                    ->helperText('Gunakan format 628xxxx'),
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



                                TextColumn::make('nama_client')
                                    ->label('Client')
                                    ->html() // penting untuk render HTML
                                    ->getStateUsing(function ($record) {
                                        $name = $record->nama ?? '';
                                        $initial = strtoupper(substr($name, 0, 1));

                                        return <<<HTML
                                        <div style="display:flex; align-items:center; gap:0.5rem;">
                                            <div style="width:50px; height:50px; display:flex; align-items:center; justify-content:center; border-radius:50%; background-color:#000; color:white; font-weight:bold;font-size: 20px;">
                                                {$initial}
                                            </div>
                                            <span style="font-size:20px;"> {$name}</span>
                                        </div>
                                        HTML;
                                    })
                                    ->searchable(),

                                // Tables\Columns\TextColumn::make('nama')
                                //     ->alignLeft()
                                //     ->searchable(),

                            ])->space(1),

                            // Bagian kanan (tanggal + QR)
                            Stack::make([

                                Tables\Columns\TextColumn::make('nomor_wa')
                                    ->label('WhatsApp')
                                    ->badge()
                                    ->color('success')
                                    ->alignRight()
                                    ->searchable(),

                                Tables\Columns\TextColumn::make('created_at')
                                    ->label('Terdaftar')
                                    ->alignRight()
                                    ->date('d M Y'),

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
                Tables\Actions\EditAction::make()->button()->label('LogService')->color('success'),
                //Tables\Actions\DeleteAction::make()->button(),
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
            RelationManagers\LogServicesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDataClients::route('/'),
            'create' => Pages\CreateDataClient::route('/create'),
            'edit' => Pages\EditDataClient::route('/{record}/edit'),
        ];
    }
}
