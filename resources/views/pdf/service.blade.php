<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">

    <title>Service Tag - #{{ $service->id }}</title>

    <style>
        @page {
            size: A4;
            margin: 3mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #000;
        }

        table {
            border-collapse: collapse;
        }

        .sticker-box {
            width: 200mm;
            height: 80mm;
            border: 2px solid #000;
        }

        .main-table {
            width: 100%;
            height: 100%;
        }

        .main-table td {
            vertical-align: top;
        }

        .border-right {
            border-right: 2px solid #000;
        }

        .border-bottom {
            border-bottom: 2px solid #000;
        }

        .header {
            background: #e5e5e5;
            border-bottom: 1px solid #000;
            padding: 4px 6px;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
        }

        .content {
            padding: 5px 6px;
            font-size: 10px;
        }

        .row {
            margin-bottom: 1px;
        }

        .label {
            display: inline-block;
            width: 70px;
            font-weight: bold;
        }

        .separator {
            display: inline-block;
            width: 10px;
            text-align: center;
        }

        .value {
            display: inline-block;
        }

        .small {
            font-size: 10px;
        }

        .bold {
            font-weight: bold;
        }

        .health-table {
            width: 90%;
        }

        .health-table td {
            padding-bottom: 3px;
            font-size: 8px;
        }

        .line {
            border-bottom: 1px solid #000;
            width: 100%;
            height: 10px;
        }

        .note-line {
            border-bottom: 1px dashed #999;
            height: 18px;
            margin-bottom: 2px;
        }

        .tech-box {
            height: 100%;
        }
    </style>
</head>

<body onload="window.print()">


    <div class="sticker-box">

        <table class="main-table">

            <!-- TOP -->
            <tr height="48%">

                <!-- DATA BARANG -->
                <td width="50%" class="border-right border-bottom">

                    <div class="header">
                        DATA BARANG
                    </div>

                    <div class="content">

                        <div class="row">
                            <span class="label">NAMA</span>
                            <span class="separator">:</span>
                            <span class="value">
                                {{ strtoupper($service->dataClient->nama ?? 'PELANGGAN') }}
                            </span>
                        </div>

                        <div class="row">
                            <span class="label">BARANG</span>
                            <span class="separator">:</span>
                            <span class="value">
                                {{ strtoupper($service->category->category ?? '-') }}
                                {{ strtoupper($service->nama_barang ?? '-') }}
                            </span>
                        </div>

                        <div class="row">
                            <span class="label">TGL MASUK</span>
                            <span class="separator">:</span>
                            <span class="value">
                                {{ $service->tanggal_masuk?->format('d/m/Y') }}
                            </span>
                        </div>

                        <div class="row">
                            <span class="label">NO WA</span>
                            <span class="separator">:</span>
                            <span class="value">
                                {{ $service->dataClient->nomor_wa ?? '-' }}
                            </span>
                        </div>

                        <div class="row">
                            <span class="label">CATATAN</span>
                            <span class="separator">:</span>
                            <span class="value">
                                {{ strtoupper($service->keterangan ?? '-') }}
                            </span>
                        </div>

                        <div class="row">
                            <span class="label">KERUSAKAN</span>
                            <span class="separator">:</span>
                            <span class="value">
                                {{ is_array($service->kerusakan)
                                    ? implode(', ', array_map('strtoupper', $service->kerusakan))
                                    : strtoupper($service->kerusakan ?? '-') }}
                            </span>
                        </div>

                    </div>

                </td>

                <!-- KELENGKAPAN -->
                <td width="25%" class="border-right border-bottom">

                    <div class="header">
                        KELENGKAPAN
                    </div>

                    <div class="content small">

                        @forelse ($service->perlengkapan ?? [] as $item)
                            <div class="row">
                                • {{ strtoupper(str_replace('_', ' ', $item)) }}
                            </div>

                        @empty

                            <div>-</div>
                        @endforelse

                    </div>

                </td>

                <!-- HEALTH -->
                <td width="25%" class="border-bottom">

                    <div class="header">
                        HEALTH CHECK
                    </div>

                    <div class="content">

                        <table class="health-table">

                            @php
                                $checks = ['KYB', 'HDD', 'SPK', 'MIC', 'CAM', 'BAT', 'WIFI'];
                            @endphp

                            @foreach ($checks as $check)
                                <tr>
                                    <td width="35%">
                                        {{ $check }}
                                    </td>

                                    <td width="65%">
                                        <div class="line"></div>
                                    </td>
                                </tr>
                            @endforeach

                        </table>

                    </div>

                </td>

            </tr>

            <!-- BOTTOM -->
            <tr height="52%">

                <td colspan="3">

                    <div class="content">
                    </div>

                </td>

            </tr>

        </table>

    </div>

</body>

</html>
