<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Service Tag - #{{ $service->id }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/bootstrap.min.css" rel="stylesheet">
    <style>
        @page {
            size: A4;
            margin: 0;
        }

        body {
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 12px;
            padding: 10px;
        }

        .sticker-box {
            width: 200mm;
            height: 80mm; /* Tinggi 1/3 A4 */
            border: 2px solid #000;
            background: #fff;
            display: flex;
            flex-direction: column;
        }

        /* Utility Borders */
        .b-bottom { border-bottom: 2px solid #000; }
        .b-right { border-right: 2px solid #000; }
        
        .header-cell {
            background-color: #f2f2f2;
            padding: 3px 8px;
            border-bottom: 1px solid #000;
            font-weight: bold;
            font-size: 10px;
        }

        .content-cell {
            padding: 8px;
        }

        .label-col {
            width: 80px;
            display: inline-block;
            font-weight: bold;
        }

        /* Layouting */
        .top-section {
            display: flex;
            border-bottom: 2px solid #000;
        }

        .bottom-section {
            display: flex;
            flex-grow: 1; /* Mengisi sisa ruang ke bawah */
        }

        .dashed-line {
            border-bottom: 1px dashed #ccc;
            height: 22px;
            width: 100%;
        }
    </style>
</head>

<body onload="window.print()">

    <div class="sticker-box">
        <!-- BARIS ATAS: DATA BARANG | KELENGKAPAN | LAPTOP HEALTH -->
        <div class="top-section">
            <!-- 1. DATA BARANG (Lebar 50%) -->
            <div style="flex: 6;" class="b-right">
                <div class="header-cell">DATA BARANG</div>
                <div class="content-cell">
                    <div class="mb-1"><span class="label-col">NAMA</span>: {{ strtoupper($service->dataClient->nama ?? 'PELANGGAN') }}</div>
                    <div class="mb-1"><span class="label-col">BARANG</span>: {{ strtoupper($service->nama_barang) }}</div>
                    <div class="mb-1"><span class="label-col">TGL MASUK</span>: {{ $service->tanggal_masuk->format('d/m/Y') }}</div>
                    <div class="mb-1"><span class="label-col">NO WA</span>: {{ $service->dataClient->nomor_wa ?? '-' }}</div>
                    <div class="mb-1"><span class="label-col">CATATAN</span>: {{ strtoupper($service->keterangan ?? '-') }}</div>
                    <div class="mb-1"><span class="label-col">KERUSAKAN</span>: <strong>{{ is_array($service->kerusakan) ? implode(', ', array_map('strtoupper', $service->kerusakan)) : strtoupper($service->kerusakan) }}</strong></div>
                </div>
            </div>

            <!-- 2. KELENGKAPAN (Lebar 25%) -->
            <div style="flex: 3;" class="b-right">
                <div class="header-cell">KELENGKAPAN</div>
                <div class="content-cell">
                    @forelse ($service->perlengkapan ?? [] as $item)
                        <div>- {{ strtoupper(str_replace('_', ' ', $item)) }}</div>
                    @empty
                        <div class="text-muted">TIDAK ADA</div>
                    @endforelse
                </div>
            </div>

            <!-- 3. LAPTOP HEALTH (Lebar 25%) -->
            <div style="flex: 3;">
                <div class="header-cell">LAPTOP HEALTH</div>
                <div class="content-cell" style="font-size: 9px; line-height: 1.4;">
                    <div class="mb-1"><span class="label-col">KEYBOARD</span>_________
                    <div class="mb-1"><span class="label-col">HARDISK</span>_________
                    <div class="mb-1"><span class="label-col">SPEAKER</span>_________
                    <div class="mb-1"><span class="label-col">MIC</span>_________
                    <div class="mb-1"><span class="label-col">KAMERA</span>_________
                    <div class="mb-1"><span class="label-col">BATTRAI</span>_________
                    <div class="mb-1"><span class="label-col">WIFI</span>_________
                </div>
            </div>
        </div>

        <!-- BARIS BAWAH: KET | TEKNISI -->
        <div class="bottom-section">
            <!-- 4. KETERANGAN (Lebar 75%) -->
            

        </div>
    </div>

</body>
</html>