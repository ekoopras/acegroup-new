<div class="space-y-4">
    <!-- Informasi Utama Client -->
    <div class="bg-slate-50 p-3 rounded-lg border border-slate-100 text-sm">
        <p class="flex justify-between">
            <span class="text-slate-500">Nama Client:</span>
            <span class="font-bold text-slate-800">{{ $service->dataClient->nama ?? '-' }}</span>
        </p>
        <p class="flex justify-between mt-1">
            <span class="text-slate-500">WhatsApp:</span>
            <span class="font-mono text-slate-800">{{ $service->dataClient->nomor_wa ?? '-' }}</span>
        </p>
    </div>

    <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">Daftar Unit Service</div>

    <!-- Looping Barang (Jika preview diarahkan ke koleksi atau ID yang baru disimpan) -->
    <div class="space-y-3">
        @php
            // Mengambil semua barang berdasarkan ID yang baru disimpan
            $allServices = \App\Models\ServiceMasuk::with('category')->whereIn('id', $this->serviceIds)->get();
        @endphp

        @foreach ($allServices as $index => $item)
            <div class="p-4 rounded-xl border border-slate-200 bg-white shadow-sm">

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
                            @if (is_array($item->kerusakan))
                                {{ implode(', ', $item->kerusakan) }}
                            @else
                                {{ $item->kerusakan ?? '-' }}
                            @endif
                        </p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-[10px] text-slate-400 uppercase font-bold">Perlengkapan</p>
                        <p class="text-slate-600 italic">
                            @if (is_array($item->perlengkapan))
                                {{ implode(', ', $item->perlengkapan) }}
                            @else
                                {{ $item->perlengkapan ?? '-' }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
