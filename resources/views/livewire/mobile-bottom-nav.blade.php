@if($showNav)
<div>
    <div class="md:hidden fixed bottom-0 left-0 z-30 w-full h-16 bg-white dark:bg-gray-900 border-t border-gray-200 flex items-center justify-around px-1 shadow-lg">

        @foreach($navItems as $item)
        <a href="{{ $item['url'] }}"
            class="relative flex flex-col items-center justify-center w-full h-full transition-all duration-200">

            {{-- Highlight Latar Belakang Soft saat Aktif --}}
            @if($item['active'])
            <span class="absolute inset-y-1.5 inset-x-1 rounded-xl"
                style="background-color: rgba(var(--primary-500), 0.12); border: 1px solid rgba(var(--primary-500), 0.25);"></span>
            @endif

            {{-- Icon dengan Warna Dinamis --}}
            <div class="relative z-10 transition-transform duration-200 {{ $item['active'] ? '-translate-y-0.5' : '' }}">
                <x-dynamic-component
                    :component="$item['icon']"
                    class="w-5 h-5"
                    style="{{ $item['active'] ? 'color: rgb(var(--primary-600));' : 'color: #9ca3af;' }}" />
            </div>

            {{-- Label Teks --}}
            <span class="relative z-10 text-[10px] tracking-tight mt-0.5 truncate px-1"
                style="{{ $item['active'] ? 'color: rgb(var(--primary-600)); font-weight: 700;' : 'color: #9ca3af; font-weight: 500;' }}">
                {{ $item['label'] }}
            </span>

            {{-- Indikator Garis Atas --}}
            @if($item['active'])
            <span class="absolute top-0 w-8 h-0.5 rounded-b-full"
                style="background-color: rgb(var(--primary-600));"></span>
            @endif
        </a>
        @endforeach

    </div>

    <style>
        @media (max-width: 768px) {
            body {
                padding-bottom: 4rem !important;
            }
        }
    </style>
</div>
@endif