@extends('android.layouts.app')
@section('title', 'Service Masuk')

@section('content')
    <div class="space-y-4">
        <!-- Search & Filter Section -->
        <div class="sticky top-16 z-30 bg-slate-50/95 backdrop-blur-sm pb-2">
            <div class="relative">
                <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                    <i data-lucide="search" class="w-4 h-4 text-slate-400"></i>
                </span>
                <input type="text"
                    class="block w-full pl-10 pr-3 py-2.5 border border-slate-200 rounded-2xl bg-white text-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent shadow-sm"
                    placeholder="Cari nomor surat atau barang...">
            </div>
        </div>

        <!-- Stats Mini Cards -->
        <div class="grid grid-cols-2 gap-3">
            <div class="bg-blue-600 rounded-2xl p-4 text-white shadow-md shadow-blue-100">
                <p class="text-[10px] uppercase font-bold opacity-80">Total Hari Ini</p>
                <p class="text-2xl font-bold">{{ $services->count() }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-slate-200 shadow-sm">
                <p class="text-[10px] uppercase font-bold text-slate-400">Menunggu Antrian</p>
                <p class="text-2xl font-bold text-slate-800">12</p>
            </div>
        </div>

        <!-- List Data -->
        <div class="space-y-4">
            @forelse($services as $item)
                <div
                    class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden active:scale-[0.98] transition-transform">
                    <!-- Card Header -->
                    <div class="px-4 py-3 border-b border-slate-50 flex justify-between items-center bg-slate-50/50">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-mono font-bold text-blue-600 bg-blue-50 px-2 py-0.5 rounded">
                                #{{ $item->nomor_surat }}
                            </span>
                            <span
                                class="text-[10px] text-slate-400 italic">{{ \Carbon\Carbon::parse($item->tanggal_masuk)->diffForHumans() }}</span>
                        </div>
                        <button class="text-slate-400">
                            <i data-lucide="more-horizontal" class="w-4 h-4"></i>
                        </button>
                    </div>

                    <!-- Card Body -->
                    <div class="p-4">
                        <div class="mb-3">
                            <h3 class="text-sm font-bold text-slate-800 uppercase tracking-tight">{{ $item->nama_barang }}
                            </h3>
                            <div class="flex items-center gap-1 text-xs text-slate-500 mt-0.5">
                                <i data-lucide="user" class="w-3 h-3"></i>
                                <span>{{ $item->nama_client }}</span>
                            </div>
                        </div>

                        <!-- Kerusakan (JSON) -->
                        <div class="space-y-2">
                            <div>
                                <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Kerusakan:</p>
                                <div class="flex flex-wrap gap-1">
                                    @php $kerusakan = is_array($item->kerusakan) ? $item->kerusakan : json_decode($item->kerusakan, true); @endphp
                                    @foreach ($kerusakan ?? [] as $rusak)
                                        <span
                                            class="bg-red-50 text-red-600 text-[10px] px-2 py-0.5 rounded-md font-medium border border-red-100">
                                            {{ $rusak }}
                                        </span>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Perlengkapan (JSON) -->
                            @if ($item->perlengkapan)
                                <div>
                                    <p class="text-[10px] font-bold text-slate-400 uppercase mb-1">Perlengkapan:</p>
                                    <div class="flex flex-wrap gap-1">
                                        @php $perlengkapan = is_array($item->perlengkapan) ? $item->perlengkapan : json_decode($item->perlengkapan, true); @endphp
                                        @foreach ($perlengkapan ?? [] as $alat)
                                            <span
                                                class="bg-slate-100 text-slate-600 text-[10px] px-2 py-0.5 rounded-md border border-slate-200">
                                                {{ $alat }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>

                        @if ($item->keterangan)
                            <div class="mt-3 p-2 bg-yellow-50 rounded-lg border border-yellow-100">
                                <p class="text-[10px] text-yellow-700 leading-relaxed">
                                    <span class="font-bold">Catatan:</span> {{ $item->keterangan }}
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Card Footer / Action -->
                    <div class="px-4 py-3 bg-slate-50 border-t border-slate-100 flex justify-between items-center">
                        <div class="flex items-center gap-3">
                            <button class="flex items-center gap-1 text-xs font-semibold text-blue-600">
                                <i data-lucide="qr-code" class="w-4 h-4"></i>
                                Label
                            </button>
                            <button class="flex items-center gap-1 text-xs font-semibold text-slate-600">
                                <i data-lucide="printer" class="w-4 h-4"></i>
                                Nota
                            </button>
                        </div>
                        <button
                            class="bg-orange-500 text-white text-[10px] px-3 py-1.5 rounded-lg font-bold shadow-sm shadow-orange-200 active:bg-orange-600">
                            PROSES SEKARANG
                        </button>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center justify-center py-12">
                    <div class="bg-slate-100 p-4 rounded-full mb-3">
                        <i data-lucide="inbox" class="w-8 h-8 text-slate-300"></i>
                    </div>
                    <p class="text-slate-400 text-sm">Belum ada data service masuk</p>
                </div>
            @endforelse
        </div>
    </div>

    <!-- Floating Action Button (FAB) -->
    <div class="fixed bottom-24 right-6 z-50">
        <button
            class="w-14 h-14 bg-blue-600 rounded-full shadow-xl shadow-blue-300 flex items-center justify-center text-white active:scale-90 transition-transform">
            <i data-lucide="plus" class="w-7 h-7"></i>
        </button>
    </div>
@endsection
