<?php

namespace App\Filament\Resources\ServiceMasukResource\Pages;

use App\Filament\Resources\ServiceMasukResource;
use App\Models\ServiceMasuk;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Rap2hpoutre\FastExcel\FastExcel;

class ListServiceMasuks extends ListRecords
{
    protected static string $resource = ServiceMasukResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),

            // Tombol Export Excel di bagian atas tabel
            Actions\Action::make('exportExcel')
                ->label('Export Excel')
                ->icon('heroicon-o-document-arrow-down')
                ->color('success')
                ->action(function () {
                    // Menggunakan generator (yield) agar hemat memori/RAM
                    function serviceGenerator()
                    {
                        foreach (ServiceMasuk::cursor() as $item) {
                            yield [
                                'no'              => $item->no,
                                'Nomor Surat'     => $item->nomor_surat,
                                'Nama Pelanggan'  => $item->nama_pelanggan,
                                'Kategori'        => $item->category?->category ?? '-',
                                'Nama Barang'     => $item->nama_barang,
                                'Tanggal Masuk'   => $item->tanggal_masuk
                                    ? Carbon::parse($item->tanggal_masuk)->format('d/m/Y')
                                    : '-',
                                'Nomor WA'        => $item->dataClient?->nomor_wa ?? '-',
                                'Kerusakan'       => $item->kerusakan,
                            ];
                        }
                    }

                    return response()->streamDownload(function () {
                        (new FastExcel(serviceGenerator()))->export('php://output');
                    }, 'service-masuk-' . date('Y-m-d') . '.xlsx');
                }),


            Actions\Action::make('print')
                ->label('Cetak Laporan')
                ->icon('heroicon-o-printer')
                ->color('info')
                ->openUrlInNewTab()
                ->url(function () {
                    // Ambil ID data dengan urutan (sortable) yang sedang aktif di tabel
                    $ids = $this->getTableQueryForExport()
                        ->pluck('id')
                        ->toArray();

                    // Kirimkan ID yang sudah terurut ke route cetak
                    return route('service-masuk.print', [
                        'ids' => implode(',', $ids),
                    ]);
                }),
        ];
    }
}
