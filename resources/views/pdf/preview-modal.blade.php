<div class="space-y-4">
    @php
        // Ambil semua data unit service berdasarkan array ID yang dilempar dari pelayanan.php
        $allServices = \App\Models\ServiceMasuk::with('category', 'dataClient')->whereIn('id', $serviceIds)->get();

        // Ambil data client dari unit pertama untuk info header modal
        $firstService = $allServices->first();
    @endphp

    <!-- Info Client (Hanya muncul jika ada data) -->
    @if ($firstService && $firstService->dataClient)
        <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 text-sm">
            <p class="flex justify-between">
                <span class="text-slate-500">Nama Client:</span>
                <span class="font-bold text-slate-800">{{ $firstService->dataClient->nama ?? '-' }}</span>
            </p>
            <p class="flex justify-between mt-1">
                <span class="text-slate-500">WhatsApp:</span>
                <span class="font-mono text-slate-800">{{ $firstService->dataClient->nomor_wa ?? '-' }}</span>
            </p>
        </div>
    @endif

    <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Daftar Unit Service</div>

    <!-- Looping Daftar Barang yang Mau Dicetak -->
    <div class="space-y-3">
        @foreach ($allServices as $item)
            <div class="p-4 rounded-xl border border-slate-200 bg-white shadow-sm flex flex-col justify-between">

                <!-- Detail Spesifikasi Unit -->
                <div class="grid grid-cols-2 gap-y-2 text-sm">
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase font-bold">Kategori</p>
                        <p class="text-slate-700">{{ $item->category->category ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-[10px] text-slate-400 uppercase font-bold">Nama Barang</p>
                        <p class="text-slate-700">{{ $item->nama_barang }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-[10px] text-slate-400 uppercase font-bold">Kerusakan</p>
                        <p class="text-slate-700">
                            {{ is_array($item->kerusakan) ? implode(', ', $item->kerusakan) : $item->kerusakan ?? '-' }}
                        </p>
                    </div>
                </div>

                <!-- Tombol Cetak Sukses Hijau Tailwind v3 di Bagian Bawah Card -->
                <div class="mt-4 pt-3 border-t border-slate-100 flex justify-end">
                    <a href="{{ route('service.print', $item->id) }}" target="_blank"
                        class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold !text-white !bg-emerald-600 rounded-lg hover:!bg-emerald-500 active:!bg-emerald-700 border border-transparent focus:outline-none focus:ring-0 transition-all duration-200 shadow-sm group/btn"
                        title="Print Stiker untuk unit ini saja">

                        <!-- Icon Printer Heroicons v2 -->
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8"
                            stroke="currentColor"
                            class="w-4 h-4 mr-2 !text-emerald-100 group-hover/btn:!text-white transition-colors duration-200">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M6.75 12A2.25 2.25 0 0 1 9 9.75h6A2.25 2.25 0 0 1 17.25 12v5.25a2.25 2.25 0 0 1-2.25 2.25H9a2.25 2.25 0 0 1-2.25-2.25V12z" />
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9.75 9.75V5.25a2.25 2.25 0 0 1 2.25-2.25h0a2.25 2.25 0 0 1 2.25 2.25v4.25m-6.75 6h7.5" />
                        </svg>

                        <span>Cetak Stiker Unit</span>
                    </a>
                </div>

            </div>
        @endforeach
    </div>
</div>
