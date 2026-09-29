<?php

namespace App\Filament\Resources\ServiceProsesResource\Pages;

use App\Filament\Resources\ServiceProsesResource;
use App\Models\ServiceProses;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Rap2hpoutre\FastExcel\FastExcel;

class ListServiceProses extends ListRecords
{
    protected static string $resource = ServiceProsesResource::class;

    public function getTabs(): array
    {

        $userCategories = auth()->user()->isSuperAdmin()
            ? null
            : auth()->user()->category()->pluck('categories.id');

        // Helper function untuk menghitung jumlah berdasarkan status terakhir + Role Divisi
        $getCount = function (string $status) use ($userCategories) {
            $query = \App\Models\ServiceProses::query();

            // Terapkan Filter Role Divisi yang sama dengan getEloquentQuery
            if ($userCategories !== null) {
                $query->whereIn('category_id', $userCategories);
            }

            return $query->whereRaw(
                'JSON_UNQUOTE(JSON_EXTRACT(log_status, CONCAT("$[", JSON_LENGTH(log_status) - 1, "].status"))) = ?',
                [$status]
            )->count();
        };

        return [
            'proses' => Tab::make('Proses')
                ->label('Proses Cek')
                ->query(fn($query) => $query->whereRaw(
                    'JSON_UNQUOTE(JSON_EXTRACT(log_status, CONCAT("$[", JSON_LENGTH(log_status) - 1, "].status"))) IN (?, ?)',
                    ['Proses Cek', 'Proses Pengerjaan']
                ))
                ->badge($getCount('Proses Cek') + $getCount('Proses Pengerjaan'))
                ->badgeColor('warning'),

            'pending' => Tab::make('Pending')
                ->label('Pending')
                ->query(fn($query) => $query->whereRaw(
                    'JSON_UNQUOTE(JSON_EXTRACT(log_status, CONCAT("$[", JSON_LENGTH(log_status) - 1, "].status"))) = ?',
                    ['Pending']
                ))
                ->badge($getCount('Pending'))
                ->badgeColor('danger'),

            'deal' => Tab::make('Deal')
                ->label('Deal Kerjakan')
                ->query(fn($query) => $query->whereRaw(
                    'JSON_UNQUOTE(JSON_EXTRACT(log_status, CONCAT("$[", JSON_LENGTH(log_status) - 1, "].status"))) = ?',
                    ['Deal Kerjakan']
                ))
                ->badge($getCount('Deal Kerjakan'))
                ->badgeColor('success'),

        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    $baseQuery = $this->getTableQuery()->with(['dataClient', 'category', 'user']);

                    $serviceGenerator = function () use ($baseQuery) {
                        foreach ($baseQuery->cursor() as $item) {

                            // --- PERBAIKAN DI SINI ---
                            // Simpan atribut log_status ke variabel lokal terlebih dahulu
                            $logStatus = $item->log_status;

                            $statusTerakhir = '-';
                            if (is_array($logStatus) && !empty($logStatus)) {
                                $lastLog = end($logStatus); // Aman digunakan pada variabel lokal
                                $statusTerakhir = $lastLog['status'] ?? '-';
                            }
                            // -------------------------

                            // Format kerusakan (Array ke String)
                            $kerusakan = is_array($item->kerusakan)
                                ? implode(', ', $item->kerusakan)
                                : $item->kerusakan;

                            // Format perlengkapan (Array ke String)
                            $perlengkapan = is_array($item->perlengkapan)
                                ? implode(', ', $item->perlengkapan)
                                : $item->perlengkapan;

                            yield [
                                'Nomor Surat'     => $item->nomor_surat,
                                'Tanggal Masuk'   => $item->tanggal_masuk?->format('d/m/Y') ?? '-',
                                'Nama Pelanggan'  => $item->nama_pelanggan,
                                'Nomor WA'        => $item->dataClient?->nomor_wa ?? '-',
                                'Kategori'        => $item->category?->category ?? '-',
                                'Nama Barang'     => $item->nama_barang,
                                'Kerusakan'       => $kerusakan,
                                'Perlengkapan'    => $perlengkapan,
                                'Keterangan'      => $item->keterangan,
                                'Status Terakhir' => $statusTerakhir,
                                'Teknisi / User'  => $item->user?->name ?? '-',
                                'Token'           => $item->token,
                            ];
                        }
                    };

                    $activeTab = $this->activeTab ?? 'semua';
                    $filename = 'service-proses-' . $activeTab . '-' . date('Y-m-d-His') . '.xlsx';

                    return response()->streamDownload(function () use ($serviceGenerator) {
                        (new FastExcel($serviceGenerator()))->export('php://output');
                    }, $filename);
                }),

            // Action Print Semua Data Service Proses
            Actions\Action::make('print')
                ->label('Cetak Laporan')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->openUrlInNewTab()
                ->url(fn() => route('service-proses.print')),
        ];
    }
}
