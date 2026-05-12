<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceMasukResource\Pages;
use App\Filament\Resources\ServiceMasukResource\RelationManagers;
use App\Models\ServiceMasuk;
use App\Models\ServiceProses;
use Filament\Forms;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ServiceMasukResource extends Resource
{
    protected static ?string $model = ServiceMasuk::class;

    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?string $navigationLabel = 'Service Masuk';
    protected static ?string $pluralLabel = 'Service Masuk';
    protected static ?string $navigationGroup = 'Transaksi';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Utama')
                    ->description('Data dasar client dan administrasi.')
                    ->schema([
                        Grid::make(3)->schema([
                            Forms\Components\TextInput::make('nomor_surat')
                                ->label('Nomor Surat')
                                ->placeholder('Otomatis...')
                                ->disabled()
                                ->columnSpan(1),

                            Forms\Components\Select::make('data_client_id')
                                ->label('Nama Client')
                                ->relationship('dataClient', 'nama')
                                ->required()
                                ->searchable()
                                ->disabled()
                                ->afterStateHydrated(function ($state, $set) {
                                    $client = \App\Models\DataClient::find($state);
                                    if ($client) {
                                        $set('nomor_wa', $client->nomor_wa);
                                    }
                                })
                                ->afterStateUpdated(function ($state, $set) {
                                    $client = \App\Models\DataClient::find($state);
                                    if ($client) {
                                        $set('nomor_wa', $client->nomor_wa);
                                    }
                                })
                                ->columnSpan(1),

                            Forms\Components\TextInput::make('nomor_wa')
                                ->label('Nomor WhatsApp')
                                ->tel()
                                ->disabled()
                                ->dehydrated(false)
                                ->placeholder('628xxx')
                                ->helperText('Gunakan format 62')
                                ->columnSpan(1),
                        ]),
                    ]),

                // BAGIAN TENGAH: SPLIT LAYOUT (BARANG & PERLENGKAPAN)
                Grid::make(3)->schema([

                    // SISI KIRI (BARANG & KERUSAKAN) - 2 Kolom
                    Group::make([
                        Section::make('Detail Unit')
                            ->schema([
                                Grid::make(2)->schema([
                                    Forms\Components\Select::make('category_id')
                                        ->label('Kategori')
                                        ->relationship('category', 'category')
                                        ->searchable()
                                        ->required(),

                                    Forms\Components\TextInput::make('nama_barang')
                                        ->label('Nama Barang')
                                        ->required()
                                        ->extraInputAttributes(['style' => 'text-transform: capitalize;']),
                                ]),

                                Forms\Components\DatePicker::make('tanggal_masuk')
                                    ->label('Tanggal Masuk')
                                    ->default(now())
                                    ->required(),

                                Forms\Components\Textarea::make('kerusakan')
                                    ->label('Deskripsi Kerusakan')
                                    ->placeholder('Contoh: Laptop mati total, sering restart sendiri...')
                                    ->required()
                                    ->rows(3),

                                Forms\Components\Textarea::make('keterangan')
                                    ->label('Keterangan Tambahan')
                                    ->placeholder('Catatan khusus teknisi...')
                                    ->rows(2),
                            ]),
                    ])->columnSpan(2),

                    // SISI KANAN (PERLENGKAPAN & QR) - 1 Kolom
                    Group::make([
                        Section::make('Fisik & Validasi')
                            ->schema([
                                Forms\Components\CheckboxList::make('perlengkapan')
                                    ->label('Perlengkapan yang Dibawa')
                                    ->options([
                                        'tas' => 'Tas',
                                        'adaptor_charger' => 'Adaptor Charger',
                                        'kabel_power' => 'Kabel Power',
                                        'kabel_usb_print' => 'Kabel USB Print',
                                        'kardus' => 'Kardus',
                                        'battrai' => 'Baterai',
                                        'kesing_kanan' => 'Kesing R',
                                        'kesing_kiri' => 'Kesing L',
                                        'usb_data' => 'USB Data',
                                    ])
                                    ->columns(1) // Satu kolom ke bawah agar rapi di sisi kanan
                                    ->bulkToggleable(), // Fitur pilih semua

                                Forms\Components\ViewField::make('qrcode')
                                    ->label('QR Code Tracking')
                                    ->view('filament.components.qrcode'),
                            ]),
                    ])->columnSpan(1),
                ]),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                Tables\Columns\TextColumn::make('nomor_surat')
                    ->searchable(),

                Tables\Columns\TextColumn::make('category.category')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama_barang')
                    ->searchable(),

                // Tables\Columns\TextColumn::make('barang')
                //     ->label('Barang')
                //     ->html()
                //     ->getStateUsing(
                //         fn($record) =>
                //         '<strong>' . e($record->category->category) . '</strong><br>' .
                //             e($record->nama_barang)
                //     )
                //     ->searchable(),

                Tables\Columns\TextColumn::make('dataClient.nama')
                    ->label('Nama Client')
                    ->searchable(),

                Tables\Columns\TextColumn::make('tanggal_masuk')
                    ->label('Masuk')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('kerusakan')
                    ->label('Daftar Kerusakan')
                    ->badge()
                    ->color('danger')
                    ->listWithLineBreaks()
                    ->wrap(),

                // Tables\Columns\ViewColumn::make('qrcode')
                //     ->label('QR')
                //     ->view('filament.tables.qrcode')
                //     ->tooltip(fn($record) => $record->nomor_surat)
                //     ->alignCenter(),

                // Tables\Columns\TextColumn::make('token')
                //     ->label('Tracking Link')
                //     ->formatStateUsing(fn($state) => route('tracking.check', ['token' => $state]))
                //     ->copyable() // Agar bisa diklik untuk copy link
                //     ->color('primary')
                //     ->icon('heroicon-o-link'),

                // Tables\Columns\TextColumn::make('nomor_wa')
                //     ->label('WhatsApp')
                //     ->badge()
                //     ->color('success')
                //     ->icon('heroicon-o-chat-bubble-left-right')
                //     ->url(fn($record) => 'https://wa.me/' . $record->nomor_wa)
                //     ->openUrlInNewTab(),

                // Tables\Columns\TextColumn::make('perlengkapan')
                //     ->badge()
                //     ->separator(',')
                //     ->limit(3),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('Kerjakan')
                    ->label('Proses')
                    ->button()
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->action(function ($record) {

                        // ⬇️ pindahkan data
                        ServiceProses::create([
                            'category_id'    => $record->category_id,
                            'data_client_id' => $record->data_client_id,
                            'nama_barang'    => $record->nama_barang,
                            'nomor_surat'    => $record->nomor_surat,
                            'qrcode'         => $record->qrcode,
                            'tanggal_masuk'  => $record->tanggal_masuk,
                            'kerusakan'      => $record->kerusakan,
                            'perlengkapan'   => $record->perlengkapan,
                            'keterangan'     => $record->keterangan,
                            'token'          => $record->token,

                            // Format harus ARRAY OF ARRAYS agar bisa dibaca Repeater
                            'log_status'     => [
                                [
                                    'status'     => 'Proses Cek',
                                    'tanggal'    => now()->toDateTimeString(),
                                    'keterangan' => 'Unit mulai dikerjakan oleh teknisi.',
                                ]
                            ],

                            'user_id'        => auth()->id(),
                        ]);

                        // ⬇️ hapus dari ServiceMasuk
                        $record->delete();
                    }),




                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('whatsapp')
                        ->label('')
                        ->button()
                        ->icon('heroicon-o-chat-bubble-left-right')
                        ->color('success') // HIJAU
                        ->url(
                            fn($record) =>
                            'https://wa.me/' . $record->dataClient->nomor_wa
                        )
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('print')
                        ->label('')
                        ->icon('heroicon-o-printer')
                        ->color('info')
                        ->button()
                        ->url(fn($record) => route('service.print', $record->id))
                        ->openUrlInNewTab(),
                    Tables\Actions\ViewAction::make()
                        ->label('')
                        ->button(),
                    Tables\Actions\EditAction::make()
                        ->label('')
                        ->button(),
                    Tables\Actions\DeleteAction::make()
                        ->label('')
                        ->button(),
                ])->label(''),


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
            'index' => Pages\ListServiceMasuks::route('/'),
            //'create' => Pages\CreateServiceMasuk::route('/create'),
            'edit' => Pages\EditServiceMasuk::route('/{record}/edit'),
        ];
    }
}
