<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Service Masuk - {{ date('d/m/Y') }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h2 {
            margin: 0;
            text-transform: uppercase;
        }

        .header p {
            margin: 5px 0 0;
            font-size: 11px;
            color: #666;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        table,
        th,
        td {
            border: 1px solid #999;
        }

        th {
            background-color: #f2f2f2;
            padding: 8px;
            text-align: left;
            font-weight: bold;
        }

        td {
            padding: 6px 8px;
            vertical-align: top;
        }

        .text-center {
            text-align: center;
        }

        /* --- SETTING PRINT A4 --- */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: A4 portrait;
                /* Set ukuran A4 Tegak */
                margin: 15mm 10mm;
                /* Margin cetak */
            }

            tr {
                page-break-inside: avoid;
                /* Mencegah baris terpotong */
            }

            thead {
                display: table-header-group;
                /* Repeat header di tiap halaman */
            }
        }
    </style>
</head>

<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()"
            style="padding: 8px 16px; background-color: #2563eb; color: white; border: none; border-radius: 4px; cursor: pointer;">
            Cetak Dokumen
        </button>
    </div>

    <div class="header">
        <h2>Laporan Data Service Masuk</h2>
        <p>Tanggal Cetak: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%" class="text-center">No</th>
                <th width="25%">Pelanggan / WA</th>
                <th width="25%">Nama Barang</th>
                <th width="15%">Tanggal</th>
                <th width="30%">Kerusakan</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $index => $item)
                @php
                    $kerusakan = is_array($item->kerusakan) ? implode(', ', $item->kerusakan) : $item->kerusakan;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>

                    <td>
                        <strong>{{ $item->nama_pelanggan }}</strong><br>
                        <small>{{ $item->dataClient?->nomor_wa ?? '-' }}</small>
                    </td>

                    <td>
                        <strong>{{ $item->category?->category ?? '-' }}</strong><br>
                        <small>{{ $item->nama_barang }}</small>
                    </td>

                    <td>
                        {{ $item->tanggal_masuk ? \Carbon\Carbon::parse($item->tanggal_masuk)->format('d/m/Y') : '-' }}
                    </td>
                    <td>{{ $kerusakan ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Tidak ada data ditemukan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>

</html>
