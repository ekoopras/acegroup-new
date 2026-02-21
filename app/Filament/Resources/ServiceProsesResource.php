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
use Filament\Resources\Components\Tabs\Tab;

class ServiceProsesResource extends Resource
{
    protected static ?string $model = ServiceProses::class;
    protected static ?int $navigationSort = 2;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-path';

    protected static ?string $navigationLabel = 'Service Proses';
    protected static ?string $pluralLabel = 'Service Proses';
    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Select::make('status')
                    ->options([
                        'Proses' => 'Proses',
                        'Pending' => 'Pending',
                        'Deal' => 'Deal',
                    ])
                    ->required(),

                Select::make('user_id')
                    ->label('Teknisi')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),

                Forms\Components\Textarea::make('keterangan')
                    ->rows(10)
                    ->columnSpanFull(),
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
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Proses' => 'warning',   // hijau
                        'Pending' => 'danger',   // merah
                        'Deal' => 'success',   // merah
                        default => 'gray',
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('keterangan')
                    ->wrap()
                    //->lineClamp(3)
                    ->extraAttributes([
                        'style' => 'max-width: 280px;',
                    ])
                    ->searchable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Teknisi')
                    ->badge()
                    ->color('success')
                    ->searchable()
                    ->sortable(),

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
                Tables\Actions\EditAction::make()->button(),
                Tables\Actions\Action::make('jadi')
                    ->label('Jadi')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->form([

                        Select::make('garansi')
                            ->options([
                                '4_hari' => '4 Hari',
                                '2_minggu' => '2 Minggu',
                                '1_bulan' => '1 Bulan',
                                '3_bulan' => '3 Bulan',
                                '1_tahun' => '1 Tahun',
                                'None' => 'None',
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
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function ($state, callable $set, callable $get) {

                                        $services = $get('../../services') ?? [];
                                        $subtotal = collect($services)->sum('biaya');

                                        $potongan = $get('../../potongan_biaya') ?? 0;

                                        $set('../../total_biaya', max($subtotal - $potongan, 0));
                                    }),
                            ])
                            ->columns(2)
                            ->minItems(1),

                        // 🔥 Tambahan Potongan
                        TextInput::make('potongan_biaya')
                            ->label('Potongan Biaya')
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->prefix('Rp')
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {

                                $services = $get('services') ?? [];
                                $subtotal = collect($services)->sum('biaya');

                                $potongan = $state ?? 0;

                                $set('total_biaya', max($subtotal - $potongan, 0));
                            }),

                        TextInput::make('total_biaya')
                            ->label('Total Biaya')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly()
                            ->dehydrated(true),

                    ])

                    ->action(function ($record, array $data) {

                        $subtotal = collect($data['services'] ?? [])
                            ->sum('biaya');

                        $potongan = $data['potongan_biaya'] ?? 0;

                        $total = max($subtotal - $potongan, 0);

                        ServiceJadi::create([
                            'category_id'     => $record->category_id,
                            'data_client_id'  => $record->data_client_id,
                            'nama_barang'     => $record->nama_barang,
                            'nomor_surat'     => $record->nomor_surat,
                            'qrcode'          => $record->qrcode,
                            'tanggal_masuk'   => $record->tanggal_masuk,
                            'tanggal_selesai' => now(),
                            'garansi'         => $data['garansi'],
                            'services'        => $data['services'],
                            'potongan_biaya'  => $potongan,
                            'total_biaya'     => $total,
                        ]);

                        $record->delete();
                    })
                    ->requiresConfirmation()
                    ->button(),

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
