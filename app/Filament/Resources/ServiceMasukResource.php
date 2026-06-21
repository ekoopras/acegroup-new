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
    protected static ?string $navigationGroup = 'Data Service';

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
                                Grid::make(1)->schema([
                                    Forms\Components\TextInput::make('nama_pelanggan')
                                        ->label('Nama Pelanggan')
                                        ->required()
                                        ->extraInputAttributes(['style' => 'text-transform: capitalize;']),
                                ]),
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

                Tables\Columns\TextColumn::make('nama_pelanggan')
                    ->label('Nama Pelanggan')
                    ->searchable(),

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

                Tables\Columns\TextColumn::make('tanggal_masuk')
                    ->label('Masuk')
                    ->date('d/m/Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('dataClient.nomor_wa')
                    ->label('Whatsapp')
                    ->sortable(),

                Tables\Columns\TextColumn::make('kerusakan')
                    ->label('Daftar Kerusakan')
                    ->badge()
                    ->color('success')
                    ->listWithLineBreaks()
                    ->wrap(),

            ])
            ->defaultPaginationPageOption(50) // Set default awal ke 10 data
            ->paginationPageOptions([50])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('')
                    ->color('primary')
                    ->button(),
                Tables\Actions\EditAction::make()
                    ->label('')
                    ->color('success')
                    ->button(),
                Tables\Actions\DeleteAction::make()
                    ->label('')
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
            'index' => Pages\ListServiceMasuks::route('/'),
            //'create' => Pages\CreateServiceMasuk::route('/create'),
            'edit' => Pages\EditServiceMasuk::route('/{record}/edit'),
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
