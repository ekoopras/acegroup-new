<div class="flex flex-col items-center gap-1">
    {{-- QR Code --}}
    <div class="w-10 h-10">
        {!! $record->qrcode !!}
    </div>

    {{-- Nomor Surat --}}
    <div class="text-xs font-semibold text-gray-700">
        {{ $record->nomor_surat }}
    </div>
</div>
