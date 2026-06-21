<?php

namespace App\Filament\App\Pages;

use App\Models\ServiceJadi;
use Filament\Pages\Page;
use Filament\Tables\Contracts\HasTable as HasTableContract;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

class UnitJadi extends Page implements HasTableContract
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Unit Jadi';
    protected static ?int $navigationSort = 3;
    protected static ?string $title = '';

    protected static string $view = 'filament.app.pages.unit-jadi';

    public function table(Table $table): Table
    {
        return $table
            ->query(ServiceJadi::query()->latest('tanggal_masuk'))
            ->contentGrid([
                'default' => 1,
                'md' => 3,
                'xl' => 3,
            ])
            ->columns([
                Stack::make([

                    // 2. BODY KARTU
                    Stack::make([
                        Split::make([
                            TextColumn::make('status')
                                ->badge()
                                ->state(function ($record) {
                                    $logs = $record->log_status;
                                    if (is_string($logs)) {
                                        $logs = json_decode($logs, true);
                                    }
                                    if (!empty($logs) && is_array($logs)) {
                                        $lastLog = end($logs);
                                        return $lastLog['status'] ?? 'Selesai';
                                    }
                                    return 'Selesai';
                                })
                                ->formatStateUsing(fn(string $state): string => trim($state))
                                ->icon(fn(string $state): string => match (strtolower(trim($state))) {

                                    'selesai'     => 'heroicon-m-check-circle', // Ikon Sudah Jadi
                                    'cancel / gagal'   => 'heroicon-m-x-circle',     // Ikon Gagal
                                    default   => 'heroicon-m-information-circle',
                                })
                                // 🛠️ FIX WARNA: Diubah ke strtolower agar warna sinkron 100%
                                ->color(fn(string $state): string => match (strtolower(trim($state))) {

                                    'selesai'     => 'success', // Hijau untuk Sukses Jadi
                                    'cancel / gagal'   => 'danger',  // Merah untuk Cancel Gagal
                                    default         => 'info',    // Biru jika tulisan di DB tidak kembar
                                })
                                ->extraAttributes(['class' => 'text-xs font-semibold font-xl text-slate-400 w-1/1']),
                        ])->extraAttributes([
                            'class' => 'py-2 border-b border-slate-100 dark:border-transparent',
                            'style' => 'border-bottom-color: #a9a9a95e;'
                        ]),

                        Split::make([
                            TextColumn::make('nama_pelanggan')
                                ->extraAttributes(['class' => 'text-xs font-semibold font-xl text-slate-400 w-1/1'])
                                ->searchable(),
                            TextColumn::make('dataClient.nomor_wa')
                                ->searchable()->formatStateUsing(fn($state) => ucwords($state))
                                ->alignEnd()
                                ->extraAttributes(['class' => 'text-sm font-bold text-slate-800 dark:text-slate-200 w-2/3']),
                        ])->extraAttributes([
                            'class' => 'py-2 border-b border-slate-100 dark:border-transparent',
                            'style' => 'border-bottom-color: #a9a9a95e;'
                        ]),

                        Split::make([
                            TextColumn::make('nama_barang')
                                ->label('Unit Barang')
                                ->formatStateUsing(fn($record) => ($record->category?->category ?? '') . ' ' . $record->nama_barang)
                                ->searchable(query: function ($query, string $search) {
                                    $query->where(function ($q) use ($search) {
                                        $q->where('nama_barang', 'like', "%{$search}%")
                                            ->orWhereHas('category', function ($catQuery) use ($search) {
                                                $catQuery->where('category', 'like', "%{$search}%");
                                            });
                                    });
                                }),
                        ])->extraAttributes([
                            'class' => 'py-2 border-b border-slate-100 dark:border-transparent',
                            'style' => 'border-bottom-color: #a9a9a95e;'
                        ]),

                        Split::make([
                            TextColumn::make('services')
                                ->label('Daftar Perbaikan / Service')
                                ->formatStateUsing(function ($state) {
                                    $namaService = $state['service'] ?? '-';
                                    return "{$namaService}";
                                })
                                ->color('gray')
                                ->badge()
                                ->color('primary')
                                ->extraAttributes(['class' => 'text-xs']),
                        ])->extraAttributes([
                            'class' => 'py-2 border-b border-slate-100 dark:border-transparent',
                            'style' => 'border-bottom-color: #a9a9a95e;'
                        ]),

                        Split::make([
                            TextColumn::make('tanggal_masuk')
                                ->description('Unit Masuk', position: 'above')
                                ->date('d/m/Y')
                                ->extraAttributes(['class' => 'text-xs text-slate-400 font-medium'])
                                ->searchable(),

                            TextColumn::make('tanggal_selesai')
                                ->description('Unit Selesai', position: 'above')
                                ->date('d/m/Y')
                                ->alignEnd()
                                ->extraAttributes(['class' => 'text-xs text-slate-400 font-medium'])
                                ->searchable(),

                        ])->extraAttributes([
                            'class' => 'py-2 border-b border-slate-100 dark:border-transparent',
                            'style' => 'border-bottom-color: #a9a9a95e;'
                        ]),

                        Split::make([
                            TextColumn::make('nomor_surat')
                                ->fontFamily('mono')
                                ->searchable()
                                ->extraAttributes(['class' => 'text-slate-900 dark:text-white']),
                            TextColumn::make('user.name')
                                ->label('Teknisi')
                                ->icon('heroicon-m-user')
                                ->color('info')
                                ->searchable() // Supaya nama teknisi bisa dicari di kolom pencarian
                                ->alignEnd(),

                        ])->extraAttributes(['class' => 'py-2']),

                    ]),

                ])->space(0),
            ])
            ->defaultPaginationPageOption(12) // Set default awal ke 10 data
            ->paginationPageOptions([12])
            ->filters([])
            ->actions([
                //
            ]);
    }
}
