<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tracking Unit #{{ $service->id }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>

<body class="bg-slate-50 text-slate-900">
    <div class="container mx-auto px-4 py-8 lg:py-12">
        <div class="max-w-2xl mx-auto">

            <!-- Header -->
            <div class="flex items-center justify-between mb-8">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight text-slate-800">Tracking Service</h1>
                    <p class="text-sm font-mono text-slate-500">ID Service: {{ $service->nomor_surat }}</p>
                </div>
                <div
                    class="h-12 w-12 bg-blue-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                    </svg>
                </div>
            </div>

            <!-- Card Utama -->
            <div class="bg-white rounded-3xl shadow-xl shadow-slate-200/60 overflow-hidden border border-slate-100">
                <div class="p-6 lg:p-8">

                    <!-- Info Barang & Pelanggan -->
                    <div class="grid grid-cols-2 gap-6 mb-8">
                        <div class="space-y-1">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Unit
                                Barang</span>
                            <p class="font-semibold text-slate-700">{{ $service->nama_barang }}</p>
                        </div>
                        <div class="space-y-1 text-right">
                            <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Nama
                                Pelanggan</span>
                            <p class="font-semibold text-slate-700">
                                {{ $status == 'antrean' ? $service->dataClient->nama ?? '-' : $service->nama_client ?? ($service->dataClient->nama ?? '-') }}
                            </p>
                        </div>
                    </div>

                    @if ($status == 'antrean')
                        <div class="bg-amber-50 border border-amber-100 rounded-2xl p-6 text-center">
                            <div
                                class="inline-flex items-center justify-center w-10 h-10 bg-amber-100 text-amber-600 rounded-full mb-3">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h5 class="text-amber-800 font-bold tracking-tight uppercase">Menunggu Antrean</h5>
                            <p class="text-amber-700/70 text-xs mt-1 px-4 leading-relaxed">Mohon tunggu teknisi kami
                                segera memproses unit Anda.</p>
                        </div>
                    @else
                        @php
                            // Definisikan status selesai atau cancel agar lebih bersih
                            $isSelesaiAtauCancel = $logs->contains(function ($log) {
                                $status = strtolower($log['status']);
                                return str_contains($status, 'selesai') ||
                                    str_contains($status, 'cancel') ||
                                    str_contains($status, 'gagal');
                            });
                        @endphp

                        <!-- Status Hero -->
                        <div class="bg-blue-600 rounded-2xl p-6 text-center text-white shadow-lg shadow-blue-100">
                            <span class="text-[10px] font-bold uppercase tracking-[0.2em] opacity-80">Status Saat
                                Ini</span>

                            <!-- 🛠️ PERBAIKAN: Ambil status dari log paling pertama -->
                            <h5 class="text-xl font-black mt-1 uppercase">
                                {{ $logs->first()['status'] ?? ($current['status'] ?? 'PROSES') }}
                            </h5>

                            <div
                                class="mt-3 inline-flex items-center gap-2 bg-blue-700/50 px-3 py-1 rounded-full text-[10px]">
                                <span class="h-2 w-2 bg-blue-300 rounded-full animate-pulse"></span>
                                <!-- 🛠️ PERBAIKAN: Ambil tanggal dari log paling pertama -->
                                Update:
                                {{ \Carbon\Carbon::parse($logs->first()['tanggal'] ?? ($current['tanggal'] ?? now()))->format('d M Y H:i') }}
                            </div>
                        </div>

                        <!-- Total Biaya -->
                        @if (isset($service->total_biaya))
                            <!-- Hapus pengecekan > 0 di sini agar kontainer tetap muncul saat biaya 0 (Cancel) -->
                            <div class="mt-8 bg-slate-50 rounded-2xl p-5 border border-slate-100">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Total
                                            Pembayaran</p>
                                        @if ($service->total_biaya > 0)
                                            <p class="text-xl font-black text-slate-800">Rp
                                                {{ number_format($service->total_biaya, 0, ',', '.') }}</p>
                                        @else
                                            <p class="text-xl font-black text-red-600 uppercase">Gratis / Cancel</p>
                                        @endif
                                    </div>

                                    @if (isset($service->garansi) && $service->garansi !== 'None' && $service->total_biaya > 0)
                                        <div
                                            class="bg-blue-100 px-3 py-1 rounded-full text-[9px] font-bold text-blue-700 uppercase">
                                            🛡️ Garansi {{ str_replace('_', ' ', $service->garansi) }}
                                        </div>
                                    @elseif($service->total_biaya == 0 && $isSelesaiAtauCancel)
                                        <div
                                            class="bg-red-100 px-3 py-1 rounded-full text-[9px] font-bold text-red-700 uppercase">
                                            ❌ Unit Batal/Gagal
                                        </div>
                                    @endif
                                </div>

                                <!-- QR Code Section: Muncul jika Selesai, Cancel, atau Gagal -->
                                @if ($isSelesaiAtauCancel)
                                    <div
                                        class="mt-6 pt-8 border-t border-dashed border-slate-200 text-center animate-in fade-in duration-700">
                                        <span
                                            class="text-[10px] font-bold uppercase tracking-[0.2em] text-slate-400 mb-4 block">
                                            QR Code Pengambilan
                                        </span>
                                        <div
                                            class="inline-block p-4 bg-white rounded-3xl shadow-sm border border-slate-100 transition-transform hover:scale-105 duration-300">
                                            {!! QrCode::size(150)->eye('square')->color(30, 41, 59)->margin(1)->generate($service->nomor_surat) !!}
                                        </div>

                                        @if ($service->total_biaya > 0)
                                            <p
                                                class="mt-4 text-[11px] text-slate-500 mx-auto max-w-[200px] leading-relaxed font-medium">
                                                Unit sudah selesai! Tunjukkan QR ini ke petugas untuk pengambilan.
                                            </p>
                                        @else
                                            <p
                                                class="mt-4 text-[11px] text-red-500 mx-auto max-w-[200px] leading-relaxed font-bold">
                                                Unit dibatalkan/gagal. Silakan ambil unit Anda kembali (Tanpa Biaya).
                                            </p>
                                        @endif
                                    </div>
                                @else
                                    <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                                        <p class="text-[10px] text-slate-400 italic">QR Code pengambilan akan muncul
                                            otomatis setelah pengerjaan selesai.</p>
                                    </div>
                                @endif
                            </div>
                        @endif

                        <!-- Timeline -->
                        <div class="mt-10">
                            <h6 class="text-sm font-bold text-slate-800 mb-6 flex items-center gap-2">
                                <span class="w-8 h-[2px] bg-slate-200"></span> Riwayat Pengerjaan
                            </h6>
                            <div
                                class="relative space-y-8 before:absolute before:inset-0 before:ml-5 before:-translate-x-px before:h-full before:w-0.5 before:bg-gradient-to-b before:from-blue-500 before:via-slate-200 before:to-transparent">
                                @foreach ($logs as $log)
                                    <div class="relative flex items-start group">
                                        <div class="absolute left-0 w-10 h-10 flex items-center justify-center">
                                            <div
                                                class="h-3 w-3 rounded-full bg-white border-2 border-blue-500 shadow-[0_0_0_4px_rgba(59,130,246,0.1)] transition-all group-first:scale-125 group-first:bg-blue-500">
                                            </div>
                                        </div>
                                        <div class="ml-12 pt-0.5">
                                            <div class="flex items-center gap-3 mb-1">
                                                <time
                                                    class="text-[10px] font-bold text-blue-500 uppercase">{{ \Carbon\Carbon::parse($log['tanggal'])->format('H:i') }}</time>
                                                <span class="text-slate-300 text-xs">•</span>
                                                <span
                                                    class="text-sm font-bold text-slate-700 uppercase">{{ $log['status'] }}</span>
                                            </div>
                                            <p class="text-xs text-slate-500 leading-relaxed">{{ $log['keterangan'] }}
                                            </p>
                                            <span
                                                class="text-[9px] text-slate-300 mt-2 block">{{ \Carbon\Carbon::parse($log['tanggal'])->format('d M Y') }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>



                    @endif
                </div>

                <!-- Footer Card -->
                <div class="bg-slate-50 px-8 py-4 border-t border-slate-100 text-center">
                    <p class="text-[10px] text-slate-400 font-medium">Hormat kami, Acegroup Service Center</p>
                </div>
            </div>

            <!-- Help Button -->
            <div class="mt-8 text-center">
                <a href="https://wa.me/62xxxxxxxx"
                    class="inline-flex items-center gap-2 text-xs font-bold text-slate-500 hover:text-blue-600 transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                    </svg>
                    Butuh bantuan teknisi? Hubungi Kami
                </a>
            </div>
        </div>
    </div>
</body>

</html>
