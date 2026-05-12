<?php

namespace App\Filament\Pages;

use App\Models\ServiceJadi;
use App\Models\LogService;
use App\Models\LaporanTransaksi;
use App\Models\User;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\DB;

class Pengambilan extends Page implements HasForms, HasActions
{
    use InteractsWithForms, InteractsWithActions;

    protected static ?string $navigationIcon = 'heroicon-o-qr-code';
    protected static string $view = 'filament.pages.pengambilan';
    protected static ?string $navigationGroup = 'Pelayanan';
    protected static ?string $navigationLabel = 'Pengambilan';

    public $search = '';
    public $unitId = null;
    public $nomor_nota = '';

    public function findUnit($nomorSurat)
    {
        // Sesuaikan 'dataClient' agar sama dengan nama fungsi di Model Anda
        $unit = ServiceJadi::with(['dataClient', 'category'])->where('nomor_surat', $nomorSurat)->first();

        if ($unit) {
            $this->unitId = $unit->id;
            Notification::make()->title('Unit Ditemukan')->success()->send();
        } else {
            $this->unitId = null;
            Notification::make()->title('Unit tidak ditemukan')->danger()->send();
        }
    }

    public function getUnitProperty()
    {
        return ServiceJadi::find($this->unitId);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bayar_cash')
                ->label('Bayar Tunai (Cash)')
                ->color('success')
                ->icon('heroicon-o-banknotes')
                ->requiresConfirmation()
                // HANYA kirim 1 argumen (metode)
                ->action(fn() => $this->prosesSelesai('cash')),

            Action::make('bayar_transfer')
                ->label('Bayar Transfer')
                ->color('info')
                ->icon('heroicon-o-credit-card')
                ->modalHeading('Pembayaran Via Transfer')
                ->modalContent(view('filament.components.rekening-info'))
                ->requiresConfirmation()
                // HANYA kirim 1 argumen (metode)
                ->action(fn() => $this->prosesSelesai('transfer')),

            Action::make('cancel_free')
                ->label('Cancel Biaya Free')
                ->color('danger')
                ->icon('heroicon-o-x-circle')
                ->requiresConfirmation()
                ->modalHeading('Konfirmasi Pengambilan Tanpa Biaya')
                ->modalDescription('Gunakan ini untuk unit yang gagal service atau dibatalkan. Unit akan langsung masuk ke riwayat (Log) tanpa tercatat di laporan keuangan.')
                ->action(fn() => $this->prosesCancelFree()),
        ];
    }

    public function prosesSelesai($metode)
    {
        // Gunakan $this->nomor_nota untuk validasi
        if (empty($this->nomor_nota)) {
            Notification::make()
                ->title('Nomor Nota Belum Diisi')
                ->body('Silakan isi nomor nota manual terlebih dahulu.')
                ->danger()
                ->send();
            return;
        }

        $unit = $this->unit;
        if (!$unit) return;

        // Ambil nilai nota dari properti class
        $nota = $this->nomor_nota;

        $userIds = collect($unit->log_status ?? [])->pluck('teknisi_id')->filter()->unique();
        $namaTeknisi = User::whereIn('id', $userIds)->pluck('name')->implode(', ');

        DB::transaction(function () use ($unit, $metode, $nota, $namaTeknisi) {

            LaporanTransaksi::create([
                'category_id'    => $unit->category_id,
                'data_client_id' => $unit->data_client_id,
                'nomor_surat'    => $unit->nomor_surat,
                'nama_barang'    => $unit->nama_barang,
                'nomor_nota'     => $nota, // Diisi dari $this->nomor_nota
                'tanggal'        => now(),
                'total_bayar'    => $unit->total_biaya,
                'metode_bayar'   => $metode,
                'teknisi'        => $namaTeknisi ?: 'Staff',
            ]);

            LogService::create([
                'category_id'         => $unit->category_id,
                'data_client_id'      => $unit->data_client_id,
                'nama_barang'         => $unit->nama_barang,
                'tanggal_pengambilan' => now(),
                'services'            => $unit->services,
                'total_biaya'         => $unit->total_biaya,
                'garansi'             => $unit->garansi,
            ]);

            $unit->delete();
        });

        Notification::make()->title('Berhasil!')->success()->send();
        $this->unitId = null;
    }

    public function prosesCancelFree()
    {
        $unit = $this->unit;
        if (!$unit) return;

        DB::transaction(function () use ($unit) {
            // Langsung simpan ke LogService
            LogService::create([
                'category_id'         => $unit->category_id,
                'data_client_id'      => $unit->data_client_id,
                'nama_barang'         => $unit->nama_barang,
                'tanggal_pengambilan' => now(),
                'services'            => $unit->services, // Riwayat alasan cancel biasanya ada di sini
                'total_biaya'         => 0, // Paksa menjadi 0 karena free
                'garansi'             => 'None',
            ]);

            // Hapus dari tabel ServiceJadi
            $unit->delete();
        });

        Notification::make()
            ->title('Unit Berhasil Diambil (Free)')
            ->body('Data telah dipindahkan ke Log Service.')
            ->success()
            ->send();

        $this->unitId = null;
    }
}
