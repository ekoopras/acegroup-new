<x-filament-panels::page>
    <div class="mobile-app-container -mx-4 -mt-4 sm:mx-0 sm:mt-0">

        <style>
            /* Merontokkan pembungkus luar Filament */
            .fi-page-main-content div.fi-card,
            .fi-main-ctn main div.fi-card,
            main .fi-card,
            .fi-ta-ctn,
            .fi-ta-content,
            div[class*="fi-ta-content"],
            .fi-ta-content-grid {
                background-color: transparent !important;
                box-shadow: none !important;
                border: none !important;
                ring: none !important;
                --tw-ring-shadow: none !important;
            }

            /* Bersihkan area header pencarian */
            .fi-ta-header,
            .fi-ta-header-ctn,
            .fi-ta-header-ctn>div {
                background-color: transparent !important;
                border: none !important;
                box-shadow: none !important;
            }

            /* Paksa search bar melebar penuh */
            .fi-ta-header-ctn {
                display: block !important;
                width: 100% !important;
                padding: 0.5rem 1rem !important;
            }

            .fi-ta-header-search,
            .fi-ta-header-search div[class*="fi-input-wrp"] {
                width: 100% !important;
                max-width: 100% !important;
            }

            .fi-ta-header-search input {
                border-radius: 1rem !important;
                background-color: #ffffff !important;
                border: 1px solid rgb(226, 232, 240) !important;
            }

            /* 🛠️ MODIFIKASI KARTU: PUTIH BERSIH DENGAN SUDUT BULAT HALUS */
            div.fi-ta-content-grid div.fi-ta-record {
                background-color: #ffffff !important;
                border-radius: 1.25rem !important;
                /* Membuat sudut membulat rapi ala gambar contoh */
                padding: 2px !important;
                /* Padding merata di dalam kartu */
                border: 1px solid rgb(240, 231, 231) !important;
                box-shadow: 0 4px 12px -2px rgb(0 0 0 / 0.03) !important;
                transition: all 0.2s ease-in-out;
            }

            /* Efek haptic ketika disentuh */
            div.fi-ta-content-grid div.fi-ta-record:active {
                transform: scale(0.98);
                background-color: rgb(249, 250, 251) !important;
            }

            /* Mode Gelap */
            .dark div.fi-ta-content-grid div.fi-ta-record {
                background-color: rgba(var(--gray-900), var(--tw-bg-opacity, 1)) !important;
                border-color: #444444 !important;
            }

            .fi-ta-pagination {
                background-color: transparent !important;
                border-top: none !important;
            }

            .fi-ta-content-grid>div {
                gap: 1.25rem !important;
                padding: 0.5rem 1rem;
            }
        </style>

        {{-- Jalankan Engine Table Filament --}}
        {{ $this->table }}
    </div>
</x-filament-panels::page>
