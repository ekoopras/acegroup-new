@php
    $nama = $record->nama ?? ''; // ambil nama DataClient
    $initial = strtoupper(substr($nama, 0, 1));
@endphp

<div class="flex items-center gap-2">
    <!-- Avatar huruf awal -->
    <div class="w-8 h-8 flex items-center justify-center rounded-full bg-blue-500 text-white font-bold">
        {{ $initial }}
    </div>

    <!-- Nama lengkap -->
    <span class="truncate">{{ $nama }}</span>
</div>
