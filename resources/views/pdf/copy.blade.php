<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <style>
        /* Ukuran kertas halaman diset ke A4 Standar Portrait */
        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #000;
        }

        table {
            border-collapse: collapse;
        }

        /* KUNCI: Lebar pas 21cm (210mm) dan Tinggi pas 10cm (100mm) berada di atas halaman */
        .sticker-box {
            width: 210mm;
            height: 80mm;
            border-bottom: 2px dashed #000;
            border-top: 2px solid #000;
            border-left: 2px solid #000;
            border-right: 2px solid #000;
            box-sizing: border-box;
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
            padding: 5px 6px;
            font-weight: bold;
            font-size: 11px;
            text-transform: uppercase;
        }

        .content {
            padding: 6px 8px;
            font-size: 11px;
        }

        .row {
            margin-bottom: 3px;
        }

        .label {
            display: inline-block;
            width: 80px;
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

        .health-table {
            width: 95%;
        }

        .health-table td {
            padding-bottom: 4px;
            font-size: 9px;
        }

        .line {
            border-bottom: 1px solid #000;
            width: 100%;
            height: 12px;
        }
    </style>
</head>

<body>

    <!-- Box ini berukuran 21cm x 10cm dan otomatis menempel di bagian paling atas kertas A4 -->
    <div class="sticker-box">
        <table class="main-table">
            <tr height="55%">
                <!-- KOLOM DATA BARANG -->
                <td width="50%" class="border-right border-bottom">
                    <div class="header">DATA BARANG</div>
                    <div class="content">
                        <div class="row">
                            <span class="label">NAMA</span>
                            <span class="separator">:</span>
                            <span class="value">{data.get('nama_pelanggan', '-')}</span>
                        </div>
                        <div class="row">
                            <span class="label">BARANG</span>
                            <span class="separator">:</span>
                            <span class="value">{data.get('category', '-')} {data.get('barang', '-')}</span>
                        </div>
                        <div class="row">
                            <span class="label">TGL MASUK</span>
                            <span class="separator">:</span>
                            <span class="value">{data.get('tanggal_masuk', '-')}</span>
                        </div>
                        <div class="row">
                            <span class="label">NO WA</span>
                            <span class="separator">:</span>
                            <span class="value">{data.get('nomor_wa', '-')}</span>
                        </div>
                        <div class="row">
                            <span class="label">CATATAN</span>
                            <span class="separator">:</span>
                            <span class="value">{data.get('keterangan', '-')}</span>
                        </div>
                        <div class="row">
                            <span class="label">KERUSAKAN</span>
                            <span class="separator">:</span>
                            <span class="value">{kerusakan}</span>
                        </div>
                    </div>
                </td>
                <!-- KOLOM KELENGKAPAN -->
                <td width="25%" class="border-right border-bottom">
                    <div class="header">KELENGKAPAN</div>
                    <div class="content small">
                        <div class="row">
                            • {perlengkapan}
                        </div>
                    </div>
                </td>
                <!-- KOLOM HEALTH CHECK -->
                <td width="25%" class="border-bottom">
                    <div class="header">HEALTH CHECK</div>
                    <div class="content">
                        <table class="health-table">
                            <tr>
                                <td width="35%">
                                    <div class="row">KYB :</div>
                                    <div class="row">HDD :</div>
                                    <div class="row">SPK :</div>
                                    <div class="row">MIC :</div>
                                    <div class="row">CAM :</div>
                                    <div class="row">BAT :</div>
                                    <div class="row">WIFI :</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                </td>
            </tr>
            <tr height="45%">
                <td colspan="3">
                    <div class="content"></div>
                </td>
            </tr>
        </table>
    </div>

</body>

</html>
