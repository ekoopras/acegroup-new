<!DOCTYPE html>
<html>

<head>
    <title>Nota Service</title>
    <style>
        body {
            font-family: Arial;
            font-size: 12px;
        }
    </style>
</head>

<body onload="window.print()">

    <h3>NOTA SERVICE</h3>
    <hr>

    <p><strong>Nama:</strong> {{ $service->nama_client }}</p>
    <p><strong>Nama:</strong> {{ $service->nama_barang }}</p>
    <p><strong>WhatsApp:</strong> {{ $service->nomor_wa }}</p>
    <p><strong>Tanggal Masuk:</strong> {{ $service->tanggal_masuk->format('d-m-Y') }}</p>
    <p><strong>Kategori:</strong> {{ $service->category->category }}</p>

    <hr>

    <p><strong>Kerusakan:</strong></p>
    <p>{{ $service->kerusakan }}</p>

    <p><strong>Perlengkapan:</strong></p>
    <ul>
        @foreach ($service->perlengkapan ?? [] as $item)
            <li>{{ ucfirst(str_replace('_', ' ', $item)) }}</li>
        @endforeach
    </ul>

    <p><strong>Keterangan:</strong></p>
    <p>{{ $service->keterangan }}</p>

    <hr>
    <p>Terima kasih telah menggunakan layanan kami</p>

</body>


</html>
