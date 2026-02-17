<?php

namespace App\Filament\App\Resources;

use App\Filament\App\Resources\ServiceMasukResource\Pages;
use App\Filament\App\Resources\ServiceMasukResource\RelationManagers;
use App\Models\ServiceMasuk;
use App\Models\ServiceProses;
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

class ServiceMasukResource extends Resource
{
    protected static ?string $model = ServiceMasuk::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([

                Forms\Components\Textarea::make('kerusakan')
                    ->label('Kerusakan')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('keterangan')
                    ->label('Keterangan Tambahan')
                    ->columnSpanFull(),


                Forms\Components\CheckboxList::make('perlengkapan')
                    ->label('Perlengkapan')
                    ->options([
                        'tas' => 'Tas',
                        'adaptor_charger' => 'Adaptor Charger',
                        'kabel_power' => 'Kabel Power',
                        'kabel_usb_print' => 'Kabel USB Print',
                        'kardus' => 'Kardus',
                        'battrai' => 'Battrai',
                        'kesing_kanan' => 'Kesing Kanan',
                        'kesing_kiri' => 'Kesing Kiri',
                        'usb_data' => 'USB Data',
                    ])
                    ->columns(2),


                Forms\Components\TextInput::make('nomor_surat')
                    ->label('Nomor Surat')
                    ->disabled(),

                Forms\Components\ViewField::make('qrcode')
                    ->label('QR Code')
                    ->view('filament.components.qrcode'),
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

                                Tables\Columns\TextColumn::make('tanggal_masuk')
                                    ->label('Masuk')
                                    ->date('d M Y')
                                    ->badge(),


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




                                Tables\Columns\TextColumn::make('kerusakan')
                                    ->wrap()
                                    ->lineClamp(3)
                                    ->extraAttributes([
                                        'style' => 'max-width: 250px;',
                                    ])
                                    ->searchable(),


                            ])->space(1),

                            // Bagian kanan (tanggal + QR)
                            Stack::make([

                                Tables\Columns\TextColumn::make('dataClient.nama')
                                    ->label('Nama Client')
                                    //->badge()
                                    //->color('danger')
                                    ->searchable()
                                    ->alignRight(),

                                Tables\Columns\TextColumn::make('dataClient.nomor_wa')
                                    ->label('Nama Client')
                                    ->badge()
                                    ->color('success')
                                    ->searchable()
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
                Tables\Actions\Action::make('Kerjakan')
                    ->button()
                    ->action(function ($record) {

                        // ⬇️ pindahkan data
                        ServiceProses::create([
                            'category_id'   => $record->category_id,
                            'data_client_id' => $record->data_client_id,
                            'nama_barang'   => $record->nama_barang,
                            'nomor_surat'   => $record->nomor_surat,
                            'qrcode'        => $record->qrcode,
                            'tanggal_masuk' => $record->tanggal_masuk,
                            'kerusakan'     => $record->kerusakan,
                            'perlengkapan'  => $record->perlengkapan,
                            'keterangan'    => $record->keterangan,
                            'status'        => 'Proses',
                        ]);

                        // ⬇️ hapus dari ServiceMasuk
                        $record->delete();
                    }),

                Tables\Actions\ViewAction::make()->button()->color('success'),


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
            'index' => Pages\ListServiceMasuks::route('/'),
            'create' => Pages\CreateServiceMasuk::route('/create'),
            'edit' => Pages\EditServiceMasuk::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (auth()->user()->isSuperAdmin()) {
            return $query; // super admin lihat semua
        }

        return $query->where('category_id', auth()->user()->category_id); // guru hanya mapel sendiri
    }
}
