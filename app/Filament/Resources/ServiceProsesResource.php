<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceProsesResource\Pages;
use App\Filament\Resources\ServiceProsesResource\RelationManagers;
use App\Models\ServiceJadi;
use App\Models\ServiceProses;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
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


                Tables\Columns\TextColumn::make('dataClient.nama')
                    ->label('Nama Client')
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
                        Select::make('garansi')
                            ->options([
                                '1_hari' => '1 Hari',
                                '7_hari' => '7 Hari',
                                '30_hari' => '30 Hari',
                                '3_bulan' => '3 Bulan',
                            ])
                            ->required(),

                        Repeater::make('services')
                            ->label('Daftar Service')
                            ->schema([
                                TextInput::make('service')
                                    ->label('Jenis Service')
                                    ->required(),

                                TextInput::make('biaya')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                        $services = $get('../../services') ?? [];

                                        $total = collect($services)->sum('biaya');

                                        $set('../../total_biaya', $total);
                                    }),
                            ])
                            ->columns(2)
                            ->minItems(1),

                        TextInput::make('total_biaya')
                            ->label('Total Biaya')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly()
                            ->dehydrated(true),


                    ])
                    ->action(function ($record, array $data) {

                        $total = collect($data['services'] ?? [])
                            ->sum('biaya');

                        ServiceJadi::create([
                            'category_id'     => $record->category_id,
                            'data_client_id' => $record->data_client_id,
                            'nama_barang'     => $record->nama_barang,
                            'nomor_surat'     => $record->nomor_surat,
                            'qrcode'          => $record->qrcode,
                            'tanggal_masuk'   => $record->tanggal_masuk,
                            'tanggal_selesai' => now(),
                            'garansi'         => $data['garansi'],
                            'services'        => $data['services'],
                            'total_biaya'     => $total,
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
