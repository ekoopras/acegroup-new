<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceProsesResource\Pages;
use App\Filament\Resources\ServiceProsesResource\RelationManagers;
use App\Models\ServiceJadi;
use App\Models\ServiceProses;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceProsesResource extends Resource
{
    protected static ?string $model = ServiceProses::class;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'Service Proses';
    protected static ?string $pluralLabel = 'Service Proses';
    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Textarea::make('keterangan')
                    ->rows(2)
                    ->columnSpanFull(),

                Forms\Components\Select::make('status')
                    ->options([
                        'Proses' => 'Proses',
                        'Pending' => 'Pending',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('barang')
                    ->label('Barang')
                    ->html()
                    ->getStateUsing(
                        fn($record) =>
                        '<strong>' . e($record->category->category) . '</strong><br>' .
                            e($record->nama_barang)
                    )
                    ->searchable(),


                Tables\Columns\TextColumn::make('nama_client')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_masuk')
                    ->label('Masuk')
                    ->date('d M Y')
                    ->badge()
                    ->sortable(),

                Tables\Columns\TextColumn::make('kerusakan')
                    ->wrap()
                    ->lineClamp(3)
                    ->extraAttributes([
                        'style' => 'max-width: 250px;',
                    ])
                    ->searchable(),

                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->searchable(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->wrap()
                    ->lineClamp(3)
                    ->extraAttributes([
                        'style' => 'max-width: 250px;',
                    ])
                    ->searchable(),

                // Tables\Columns\ViewColumn::make('qrcode')
                //     ->label('QR')
                //     ->view('filament.tables.qrcode')
                //     ->tooltip(fn($record) => $record->nomor_surat)
                //     ->alignCenter(),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('jadi')
                    ->label('Jadi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([
                        Forms\Components\TextInput::make('jasa_service')
                            ->label('Jasa Service')
                            ->required(),

                        Forms\Components\TextInput::make('biaya')
                            ->numeric()
                            ->prefix('Rp')
                            ->required(),

                        Forms\Components\Textarea::make('catatan')
                            ->rows(2),
                    ])
                    ->action(function ($record, array $data) {
                        ServiceJadi::create([
                            'category_id'      => $record->category_id,
                            'nama_barang'      => $record->nama_barang,
                            'nama_client'      => $record->nama_client,
                            'nomor_wa'         => $record->nomor_wa,
                            'nomor_surat'      => $record->nomor_surat,
                            'qrcode'           => $record->qrcode,
                            'tanggal_masuk'    => $record->tanggal_masuk,
                            'tanggal_selesai'  => now(),
                            'kerusakan'        => $record->kerusakan,
                            'jasa_service'     => $data['jasa_service'],
                            'biaya'            => $data['biaya'],
                            'catatan'          => $data['catatan'] ?? null,
                        ]);

                        // hapus dari proses
                        $record->delete();
                    })
                    ->requiresConfirmation(),

                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListServiceProses::route('/'),
            //'create' => Pages\CreateServiceProses::route('/create'),
            //'edit' => Pages\EditServiceProses::route('/{record}/edit'),
        ];
    }
}
