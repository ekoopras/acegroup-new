<div class="space-y-2 text-sm">
    <p><b>Nama Client:</b> {{ $service->nama_client }}</p>
    <p><b>Barang:</b> {{ $service->nama_barang }}</p>
    <p><b>Kategori:</b> {{ $service->category->category }}</p>
    <p><b>Kerusakan:</b> {{ $service->kerusakan }}</p>
    <p><b>Tanggal Masuk:</b> {{ $service->tanggal_masuk->format('d-m-Y') }}</p>
</div>
