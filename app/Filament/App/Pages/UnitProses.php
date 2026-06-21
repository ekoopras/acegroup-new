<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Actions\UnitProsesAction\CancelAction;
use App\Filament\App\Actions\UnitProsesAction\ChatWaAction;
use App\Filament\App\Actions\UnitProsesAction\InputKerusakanAction;
use App\Filament\App\Actions\UnitProsesAction\JadiAction;
use App\Filament\App\Widgets\StatusFilterWidget;
use App\Models\ServiceProses;
use Filament\Forms\Components\ToggleButtons;
use Filament\Pages\Page;
use Filament\Resources\Components\Tab;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Contracts\HasTable as HasTableContract;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;


class UnitProses extends Page implements HasTableContract
{
    use InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationLabel = 'Unit Proses';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = '';

    protected static string $view = 'filament.app.pages.unit-proses';

    public function table(Table $table): Table
    {
        $query = ServiceProses::query(); // Sesuaikan nama model Anda

        // 2. Suntikkan logika pembatasan Kategori (Sama seperti getEloquentQuery kemarin)
        if (! auth()->user()->isSuperAdmin()) {
            $query->whereIn(
                'category_id',
                auth()->user()->category()->pluck('categories.id')
            );
        }

        return $table
            ->query($query)
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
                                // 🛠️ FIX: Ambil properti JSON ke variabel biasa dulu agar tidak memicu eror pointer PHP
                                ->state(function ($record) {
                                    $logs = $record->log_status;

                                    if (!empty($logs) && is_array($logs)) {
                                        $lastLog = end($logs); // Aman karena dilempar ke variabel array murni
                                        return $lastLog['status'] ?? 'Proses Cek';
                                    }

                                    return 'Proses Cek';
                                })
                                ->formatStateUsing(fn(string $state): string => trim($state))
                                // 🛠️ FIX ICON: Diubah ke strtolower agar kebal huruf besar/kecil
                                ->icon(fn(string $state): string => match (strtolower(trim($state))) {
                                    'proses cek'  => 'heroicon-m-magnifying-glass',
                                    'pending' => 'heroicon-m-clock',
                                    'deal kerjakan'    => 'heroicon-m-wrench-screwdriver',
                                    default   => 'heroicon-m-information-circle',
                                })
                                // 🛠️ FIX WARNA: Diubah ke strtolower agar warna sinkron 100%
                                ->color(fn(string $state): string => match (strtolower(trim($state))) {
                                    'proses cek'    => 'warning', // Kuning/Oranye
                                    'pending'       => 'danger',  // Merah
                                    'deal kerjakan' => 'success', // Hijau
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
                            TextColumn::make('kerusakan')
                                ->badge()
                                ->color('success')
                                ->separator(',')
                                ->extraAttributes(['class' => 'w-2/3 flex flex-wrap gap-1'])
                                ->searchable(),
                        ])->extraAttributes([
                            'class' => 'py-2 border-b border-slate-100 dark:border-transparent',
                            'style' => 'border-bottom-color: #a9a9a95e;'
                        ]),

                        Split::make([
                            // TextColumn::make('nomor_surat')
                            //     ->fontFamily('mono')
                            //     ->searchable()
                            //     ->extraAttributes(['class' => 'text-slate-900 dark:text-white']),

                            TextColumn::make('user.name')
                                ->label('Teknisi')
                                ->icon('heroicon-m-user')
                                ->color('info')
                                ->searchable(), // Supaya nama teknisi bisa dicari di kolom pencarian

                            TextColumn::make('tanggal_masuk')
                                ->date('d/m/Y')
                                ->alignEnd()
                                ->extraAttributes(['class' => 'text-xs text-slate-400 font-medium'])
                                ->searchable(),

                        ])->extraAttributes(['class' => 'py-2']),

                    ]),

                ])->space(0),
            ])
            ->defaultPaginationPageOption(12) // Set default awal ke 10 data
            ->paginationPageOptions([12])
            ->filters([
                Filter::make('status_filter')
                    ->form([
                        ToggleButtons::make('status_unit')
                            ->label('')
                            ->options([
                                'Proses Cek' => 'Proses Cek',
                                'Pending' => 'Pending',
                                'Deal Kerjakan' => 'Deal',
                                'kerjaan_saya' => 'Kerjaan Saya', // 🚀 OPSI KE-4 NYELIP DI SINI
                            ])
                            ->colors([
                                'Proses Cek' => 'warning',
                                'Pending' => 'danger',
                                'Deal Kerjakan' => 'success',
                                'kerjaan_saya' => 'info', // Warna biru untuk pembeda
                            ])
                            ->icons([
                                'Proses Cek' => 'heroicon-m-magnifying-glass',
                                'Pending' => 'heroicon-m-clock',
                                'Deal Kerjakan' => 'heroicon-m-wrench-screwdriver',
                                'kerjaan_saya' => 'heroicon-m-user', // Icon user / teknisi
                            ])
                            ->inline()

                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        $pilihan = $data['status_unit'] ?? null;

                        return $query->when(
                            $pilihan,
                            function (Builder $q, $value) {
                                // 🔍 PERAKONDISIAN LOGIC FILTER
                                if ($value === 'kerjaan_saya') {
                                    // JIKA DIKLIK KERJAAN SAYA: Filter berdasarkan ID user yang login
                                    return $q->whereRaw(
                                        "JSON_UNQUOTE(JSON_EXTRACT(log_status, CONCAT('$[', JSON_LENGTH(log_status) - 1, '].user_id'))) = ?",
                                        [auth()->id()]
                                    );
                                } else {
                                    // JIKA DIKLIK OPSI STATUS: Filter berdasarkan nama status seperti biasa
                                    return $q->whereRaw(
                                        "JSON_UNQUOTE(JSON_EXTRACT(log_status, CONCAT('$[', JSON_LENGTH(log_status) - 1, '].status'))) = ?",
                                        [$value]
                                    );
                                }
                            }
                        );
                    }),
            ])
            ->actions([

                InputKerusakanAction::make(),
                ChatWaAction::make(),
                JadiAction::make(),
                CancelAction::make(),

                // Action::make('printProses')
                //     ->label('')
                //     ->icon('heroicon-o-printer')
                //     ->color('info')
                //     ->button()
                //     ->url(fn($record) => route('service.print.proses', $record->id))
                //     ->openUrlInNewTab(),

            ]);
    }
}
