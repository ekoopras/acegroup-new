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
            font-size: 13px;
            padding: 10px;
        }

        .sticker-box {
            width: 198mm;
            height: 100mm;
            /* 1/3 A4 */
            border: 3px solid #000;
            background: #fff;
            display: flex;
            flex-direction: column;
            border-radius: 10px;
        }

        .header-section {
            border-bottom: 3px solid #000;
            padding: 8px 0;
            text-align: center;
        }

        .main-info-section {
            display: flex;
            border-bottom: 2px solid #000;
            flex: 0 0 auto;
            /* Ukuran mengikuti konten */
        }

        .info-left {
            flex: 7;
            padding: 10px;
            border-right: 2px solid #000;
        }

        .info-right {
            flex: 5;
            padding: 10px;
        }

        .ket-section {
            flex: 1;
            /* Mengambil sisa ruang yang ada agar lebih tinggi */
            padding: 10px;
            display: flex;
            flex-direction: column;
        }

        .ket-content {
            flex: 1;
            border: 1px solid #ddd;
            margin-top: 5px;
            padding: 8px;
            position: relative;
        }

        .info-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .label-col {
            width: 100px;
            font-weight: bold;
        }

        .dashed-line {
            border-bottom: 1px dashed #999;
            height: 25px;
            width: 100%;
        }
    </style>
</head>

<body onload="window.print()">

    <div class="sticker-box">

        <!-- INFO BARANG & KELENGKAPAN (KANAN KIRI) -->
        <div class="main-info-section">
            <!-- SISI KIRI: INFO BARANG -->
            <div class="info-left">
                <table class="w-100 info-table">
                    <tr>
                        <td class="label-col">NO NOTA</td>
                        <td>: {{ $service->id }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">NAMA</td>
                        <td>: {{ strtoupper($service->nama_client) }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">BARANG</td>
                        <td>: {{ strtoupper($service->nama_barang) }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">TGL MASUK</td>
                        <td>: {{ $service->tanggal_masuk->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td class="label-col">TROUBLE</td>
                        <td class="fw-bold">:
                            {{ is_array($service->kerusakan) ? implode(', ', array_map('strtoupper', $service->kerusakan)) : strtoupper($service->kerusakan) }}
                        </td>
                    </tr>
                    <tr>
                        <td class="label-col">NO. WA</td>
                        <td>: {{ $service->nomor_wa }}</td>
                    </tr>
                </table>
            </div>

            <!-- SISI KANAN: KELENGKAPAN -->
            <div class="info-right">
                <p class="fw-bold mb-1" style="text-decoration: underline;">KELENGKAPAN :</p>
                <div style="font-size: 11px;">
                    @if (!empty($service->perlengkapan) && is_array($service->perlengkapan))
                        @foreach ($service->perlengkapan as $item)
                            <div>[✓] {{ strtoupper(str_replace('_', ' ', $item)) }}</div>
                        @endforeach
                    @else
                        <div class="text-muted">- TIDAK ADA -</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- AREA KETERANGAN (TINGGI) -->
        <div class="ket-section">
            <p class="fw-bold mb-0">CATATAN / LOG TEKNISI :</p>
            <div>
                @if ($service->keterangan)
                    <div class="mb-2">{{ $service->keterangan }}</div>
                @endif
            </div>
        </div>
    </div>

</body>

</html>
