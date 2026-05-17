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

    protected static ?int $navigationSort = 1;
    protected static ?string $navigationIcon = 'heroicon-o-fire';
    protected static ?string $navigationLabel = 'Service Masuk';
    protected static ?string $pluralLabel = 'Service Masuk';

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

                                Tables\Columns\TextColumn::make('category.category')
                                    ->alignLeft()
                                    ->searchable(),

                                Tables\Columns\TextColumn::make('nama_barang')
                                    ->alignLeft()
                                    ->searchable(),

                                Tables\Columns\TextColumn::make('kerusakan')
                                    ->lineClamp(3)
                                    ->extraAttributes([
                                        'style' => 'max-width: 250px;',
                                    ])
                                    ->badge()
                                    ->color('danger')
                                    ->listWithLineBreaks()
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

                                Tables\Columns\TextColumn::make('tanggal_masuk')
                                    ->label('Masuk')
                                    ->date('d M Y')
                                    ->badge()
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

                Tables\Actions\Action::make('print')
                    ->label('Print')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->button()
                    ->url(fn($record) => route('service.print', $record->id))
                    ->openUrlInNewTab(),

                Tables\Actions\Action::make('whatsapp')
                    ->label('Chat Dia')
                    ->button()
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->color('success')
                    ->url(function ($record) {
                        // 1. Ambil data dari relasi
                        $client = $record->dataClient;
                        $categoryName = $record->category->category ?? 'Unit';

                        // 2. Format Kerusakan & Perlengkapan (Cek jika array atau string)
                        $kerusakanText = is_array($record->kerusakan) ? implode(', ', $record->kerusakan) : ($record->kerusakan ?? '-');
                        $perlengkapanText = is_array($record->perlengkapan) ? implode(', ', $record->perlengkapan) : ($record->perlengkapan ?? '-');

                        // 3. Link Tracking
                        $linkTracking = url("/tracking/{$record->token}");

                        // 4. Susun Pesan
                        $pesan = "Asallamuallaikum *{$client->nama}*\n\n";
                        $pesan .= "Service anda sudah kami terima. Berikut rincian unit anda:\n\n";

                        $pesan .= "Unit: *{$categoryName} {$record->nama_barang}*\n";
                        $pesan .= "No. Surat: {$record->nomor_surat}\n";
                        $pesan .= "Trouble: {$kerusakanText}\n";
                        $pesan .= "Kelengkapan: {$perlengkapanText}\n";
                        $pesan .= "Tracking: {$linkTracking}\n";
                        $pesan .= "----------------------------\n\n";

                        $pesan .= "Untuk pengambilan unit akan kami infokan kembali dengan QR Code pengambilan.\n\n";
                        $pesan .= "Hormat kami,\n*Acegroup Service Center*";

                        // 5. Format Nomor WA (Ubah 0 jadi 62)
                        $nomor_wa = preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $client->nomor_wa));

                        // 6. Return Link WhatsApp
                        return "https://api.whatsapp.com/send?phone={$nomor_wa}&text=" . urlencode($pesan);
                    })
                    ->openUrlInNewTab(),


                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make()
                        ->label('')
                        ->button(),
                    Tables\Actions\DeleteAction::make()
                        ->label('')
                        ->button(),
                ])->label(''),



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

        //return $query->where('mapel_id', auth()->user()->mapel_id); // guru hanya mapel sendiri

        return $query->whereIn(
            'category_id',
            auth()->user()->category()->pluck('categories.id')
        );
    }
}
