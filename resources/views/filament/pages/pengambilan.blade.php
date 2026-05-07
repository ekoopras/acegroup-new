<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

        <!-- Bagian Kiri: Scanner -->
        <x-filament::section>
            <x-slot name="heading">Scan QR Nota</x-slot>

            <div id="reader" style="width: 100%; border: none;"></div>

            <div class="mt-4">
                <x-filament::input.wrapper>
                    <x-filament::input type="text" placeholder="Atau ketik nomor surat manual..."
                        wire:model.defer="search" wire:keydown.enter="findUnit($event.target.value)" />
                </x-filament::input.wrapper>
            </div>
        </x-filament::section>

        <!-- Bagian Kanan: Detail Unit -->
        <x-filament::section>
            <x-slot name="heading">Detail Pembayaran</x-slot>

            @if ($unit)
                <div class="space-y-4">
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Nomor Nota:</span>
                        <span class="font-bold">{{ $unit->nomor_surat }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Unit:</span>
                        <span class="font-bold">{{ $unit->nama_barang }}</span>
                    </div>
                    <div class="flex justify-between border-b pb-2">
                        <span class="text-gray-500">Total Biaya:</span>
                        <span class="text-xl font-bold text-primary-600">Rp
                            {{ number_format($unit->total_biaya, 0, ',', '.') }}</span>
                    </div>

                    <div class="pt-4">
                        {{ $this->bayar }}
                    </div>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                    <x-heroicon-o-qr-code class="w-16 h-16 mb-4" />
                    <p>Silakan scan QR nota pelanggan untuk memunculkan data pengerjaan.</p>
                </div>
            @endif
        </x-filament::section>
    </div>

    <!-- Script Scanner -->
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        function onScanSuccess(decodedText, decodedResult) {
            // Bunyi Beep sederhana
            let audio = new Audio('https://www.soundjay.com/button/beep-07.mp3');
            audio.play();

            // Kirim data ke Livewire
            @this.findUnit(decodedText);
        }

        let html5QrcodeScanner = new Html5QrcodeScanner(
            "reader", {
                fps: 10,
                qrbox: 250
            }
        );
        html5QrcodeScanner.render(onScanSuccess);
    </script>
</x-filament-panels::page>
