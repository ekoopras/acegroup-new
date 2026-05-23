<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ServiceProsesResource\Pages;
use App\Filament\Resources\ServiceProsesResource\RelationManagers;
use App\Models\ServiceJadi;
use App\Models\ServiceProses;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\Components\Tabs\Tab;
use Illuminate\Support\Facades\DB;

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

                Section::make('Log Perkembangan')
                    ->description('Tambahkan riwayat pengecekan dan pengerjaan di sini')
                    ->schema([
                        Repeater::make('log_status')
                            ->label('Update Progres')
                            ->schema([
                                Select::make('status')
                                    ->options([
                                        'Proses Cek' => 'Proses Cek',
                                        'Pending' => 'Pending (Menunggu Part/Konfirmasi)',
                                        'Deal' => 'Deal (Pengerjaan Disetujui)',
                                        'Proses Pengerjaan' => 'Proses Pengerjaan',
                                        'Trial' => 'Trial',
                                        'Selesai' => 'Selesai',
                                    ])
                                    ->required()
                                    ->native(false),

                                DateTimePicker::make('tanggal')
                                    ->default(now())
                                    ->required(),

                                Select::make('teknisi_id')
                                    ->label('Teknisi')
                                    ->options(User::all()->pluck('name', 'id')) // Mengambil nama dari UserResource
                                    ->default(auth()->id())
                                    ->searchable()
                                    ->native(false),

                                Textarea::make('keterangan')
                                    ->placeholder('Contoh: Sedang mengganti IC Power...')
                                    ->rows(2)
                                    ->columnSpanFull(),


                            ])
                            ->columns(3)
                            ->collapsible() // Bisa diciutkan agar rapi
                            ->cloneable() // Mempermudah teknisi jika keterangan mirip
                            ->addActionLabel('Tambah Update Baru')
                            ->reorderableWithButtons(), // Urutan bisa diatur
                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Tables\Columns\TextColumn::make('barang')
                //     ->label('Barang')
                //     ->html()
                //     ->getStateUsing(
                //         fn($record) =>
                //         '<strong>' . e($record->category->category) . '</strong><br>' .
                //             e($record->nama_barang)
                //     )
                //     ->searchable(),

                Tables\Columns\TextColumn::make('category.category')
                    ->searchable(),

                Tables\Columns\TextColumn::make('nama_barang')
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
                    ->label('Daftar Kerusakan')
                    ->badge()
                    ->color('danger')
                    ->listWithLineBreaks()
                    ->wrap(),

                Tables\Columns\TextColumn::make('log_status')
                    ->label('Status Terakhir')
                    ->badge()
                    ->getStateUsing(function ($record) {
                        // Mengambil array log_status
                        $logs = $record->log_status;

                        // Pastikan logs adalah array dan tidak kosong
                        if (is_array($logs) && !empty($logs)) {
                            // Mengambil elemen terakhir dari array
                            $lastLog = end($logs);
                            return $lastLog['status'] ?? '-';

                            $teknisiName = User::find($lastLog['teknisi_id'])?->name ?? 'No Name';
                            return "$status ($teknisiName)";
                        }

                        return 'Belum ada status';
                    })
                    ->color(fn(string $state): string => match ($state) {
                        'Proses Cek', 'Proses Pengerjaan' => 'warning',
                        'Pending' => 'danger',
                        'Deal', 'Selesai' => 'success',
                        default => 'gray',
                    })
                    // Opsional: Menampilkan keterangan terakhir di bawah status sebagai info tambahan
                    ->description(function ($record) {
                        $logs = $record->log_status;
                        if (is_array($logs) && !empty($logs)) {
                            $lastLog = end($logs);
                            return $lastLog['keterangan'] ?? '';
                        }
                        return null;
                    })
                    ->searchable(),

                Tables\Columns\TextColumn::make('teknisi_terakhir')
                    ->label('Teknisi')
                    ->badge()
                    ->color('success')
                    ->getStateUsing(function ($record) {
                        $logs = $record->log_status;
                        if (is_array($logs) && !empty($logs)) {
                            $lastLog = end($logs);
                            // Ambil ID teknisi dari log terakhir
                            $teknisiId = $lastLog['teknisi_id'] ?? null;

                            if ($teknisiId) {
                                return \App\Models\User::find($teknisiId)?->name ?? 'Anonim';
                            }
                        }
                        return 'Belum Ditentukan';
                    }),

                // Tables\Columns\TextColumn::make('token')
                //     ->label('Tracking Link')
                //     ->formatStateUsing(fn($state) => route('tracking.check', ['token' => $state]))
                //     ->copyable() // Agar bisa diklik untuk copy link
                //     ->color('primary')
                //     ->icon('heroicon-o-link'),


            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
                    ->label('Pilihan') // Mengubah tulisan tombol utama (opsional)
                    ->icon('heroicon-m-ellipsis-vertical') // Icon titik tiga vertikal (opsional)
                    ->color('gray') // Warna tombol utama (opsional)
                    ->tooltip('Aksi Data'),
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
