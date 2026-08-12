<x-filament-widgets::widget>
    <div style="display: flex; flex-direction: column; gap: 8px;">
        @php
        $menus = [
        [
        'label' => 'Pelayanan',
        'url' => url('/app/pelayanan'),
        'icon' => 'heroicon-o-wrench-screwdriver',
        'bg' => 'bg-amber-500/10',
        'color' => '#f59e0b', // Amber / Orange
        ],
        [
        'label' => 'Pengambilan',
        'url' => url('/app/pengambilan'),
        'icon' => 'heroicon-o-qr-code',
        'bg' => 'bg-emerald-500/10',
        'color' => '#10b981', // Hijau Emerald
        ],
        [
        'label' => 'Antrian',
        'url' => url('/app/antrian-masuk'),
        'icon' => 'heroicon-o-inbox-arrow-down',
        'bg' => 'bg-sky-500/10',
        'color' => '#0ea5e9', // Biru Cerah
        ],
        [
        'label' => 'Proses',
        'url' => url('/app/unit-proses'),
        'icon' => 'heroicon-o-cpu-chip',
        'bg' => 'bg-indigo-500/10',
        'color' => '#6366f1', // Indigo
        ],
        [
        'label' => 'Unit Jadi',
        'url' => url('/app/unit-jadi'),
        'icon' => 'heroicon-o-check-circle',
        'bg' => 'bg-rose-500/10',
        'color' => '#f43f5e', // Rose / Merah Muda
        ],
        [
        'label' => 'Log Service',
        'url' => url('/app/data-clients'),
        'icon' => 'heroicon-o-document-text',
        'bg' => 'bg-purple-500/10',
        'color' => '#a855f7', // Ungu
        ],
        ];
        @endphp

        {{-- Grid 3 Kolom --}}
        <div style="display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 10px;">
            @foreach($menus as $menu)
            <a href="{{ $menu['url'] }}"
                class="group relative flex flex-col items-center justify-center text-center p-3 rounded-xl bg-white dark:bg-gray-900 border border-gray-100 dark:border-gray-800 shadow-sm active:scale-[0.97] transition-all select-none overflow-hidden">

                {{-- Icon Box dengan Background Soft --}}
                <div class="flex items-center justify-center w-10 h-10 rounded-xl mb-2 {{ $menu['bg'] }} group-hover:scale-105 transition-transform">
                    {{-- Ikon Menggunakan Inline Style Color Agar Pasti Berubah --}}
                    <x-dynamic-component
                        :component="$menu['icon']"
                        class="w-6 h-6"
                        style="color: {{ $menu['color'] }} !important;" />
                </div>

                {{-- Label --}}
                <div class="w-full min-w-0">
                    <h4 class="text-xs font-semibold text-gray-800 dark:text-white truncate">
                        {{ $menu['label'] }}
                    </h4>
                </div>

                {{-- Touch Feedback --}}
                <span class="absolute inset-0 bg-black/5 dark:bg-white/5 opacity-0 active:opacity-100 transition-opacity pointer-events-none rounded-xl"></span>
            </a>
            @endforeach
        </div>

    </div>
</x-filament-widgets::widget>