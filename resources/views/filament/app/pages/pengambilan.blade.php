<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Bagian Kiri: Scanner -->
        <x-filament::section>
            <x-slot name="heading">Scan QR Nota</x-slot>
            <div id="reader" style="width: 100%; min-height: 300px;" class="overflow-hidden rounded-lg bg-gray-100">
            </div>
            <div class="mt-4 flex gap-2">
                <x-filament::input.wrapper class="flex-1">
                    <x-filament::input type="text" placeholder="Atau ketik nomor surat manual..." wire:model="search"
                        wire:keydown.enter="findUnit($event.target.value)" />
                </x-filament::input.wrapper>
                <x-filament::button color="gray" onclick="startScanner()">Reset</x-filament::button>
            </div>
        </x-filament::section>

        <!-- Bagian Kanan: Detail Unit -->
        <x-filament::section>
            <x-slot name="heading">Detail Unit & Pembayaran</x-slot>
            <div wire:loading.flex class="justify-center items-center py-10">
                <x-filament::loading-indicator class="h-10 w-10" />
            </div>

            <div wire:loading.remove>
                @if ($this->unitId && $this->unit)
                    <div class="space-y-6">
                        <!-- Data Unit (Kategori, Client, dll) -->
                        <div class="grid grid-cols-2 gap-4 text-sm">
                            <div class="border-b pb-2">
                                <span class="text-gray-500 block text-xs">Pelanggan:</span>
                                <span class="font-bold text-lg">{{ $this->unit->nama_pelanggan ?? 'N/A' }}</span>
                            </div>
                            <div class="border-b pb-2">
                                <span class="text-gray-500 block text-xs">Unit Barang</span>
                                <span class="font-bold text-lg">{{ $this->unit->category->category ?? 'N/A' }}
                                    {{ $this->unit->nama_barang }}</span>
                            </div>
                        </div>

                        <!-- Rincian Service (Safe Key Check) -->
                        <div class="bg-gray-50 dark:bg-gray-900 p-4 rounded-lg">
                            <span class="text-xs font-bold uppercase text-gray-400">Rincian Service:</span>
                            <ul class="text-sm mt-1 space-y-1">
                                @foreach ($this->unit->services ?? [] as $service)
                                    <li class="flex justify-between">
                                        <span>{{ $service['service'] ?? ($service['nama_service'] ?? 'Jasa') }}</span>
                                        <span>Rp {{ number_format($service['biaya'] ?? 0, 0, ',', '.') }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                        <!-- TOTAL BIAYA -->
                        <div
                            class="flex justify-between items-center bg-success-50 p-4 rounded-lg border border-success-200">
                            <span class="font-bold text-success-700">Total Biaya:</span>
                            <span class="text-3xl font-black text-success-600">
                                Rp {{ number_format($this->unit->total_biaya, 0, ',', '.') }}
                            </span>
                        </div>

                        <!-- TOMBOL PEMBAYARAN -->
                        <div class="grid grid-cols-2 gap-4">
                            {{ $this->bayar_cash }}
                            {{ $this->bayar_transfer }}
                            {{ $this->cancel_free }}
                        </div>
                    </div>
                @else
                    <div
                        class="flex flex-col items-center justify-center py-12 text-gray-400 border-2 border-dashed rounded-lg">
                        <x-heroicon-o-qr-code class="w-16 h-16 mb-4 opacity-20" />
                        <p>Silakan scan QR nota untuk memunculkan data.</p>
                    </div>
                @endif
            </div>
        </x-filament::section>
    </div>

    <!-- Scripts Tetap Sama -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let html5QrCode;

        function startScanner() {
            if (html5QrCode) {
                html5QrCode.stop().catch(err => console.log(err));
            }
            html5QrCode = new Html5Qrcode("reader");
            const config = {
                fps: 10,
                qrbox: {
                    width: 250,
                    height: 250
                }
            };
            html5QrCode.start({
                facingMode: "environment"
            }, config, (decodedText) => {
                html5QrCode.stop().then(() => {
                    @this.findUnit(decodedText);
                    new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3').play();
                });
            }).catch(err => {
                console.error(err);
            });
        }
        document.addEventListener('DOMContentLoaded', startScanner);
        document.addEventListener('livewire:load', startScanner);
    </script>
</x-filament-panels::page>
