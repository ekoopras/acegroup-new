<?php

namespace App\Livewire;

use Livewire\Component;

class MobileBottomNav extends Component
{
    // Status untuk menampilkan/menyembunyikan bottom nav
    public bool $showNav = true;

    // Listener saat modal Filament dibuka

    public function hideNav(): void
    {
        $this->showNav = false;
    }

    // Listener saat modal Filament ditutup
    public function displayNav(): void
    {
        $this->showNav = true;
    }

    public function render()
    {
        // Cek 1: Sembunyikan jika user belum login
        // Cek 2: Cek apakah berada di halaman auth (Login/Register)
        if (! auth()->check() || request()->routeIs('filament.*.auth.*')) {
            return <<<'HTML'
            <div></div>
            HTML;
        }

        $navItems = [
            [
                'label' => 'Beranda',
                'url' => url('/app'),
                'active' => request()->is('app'),
                'icon' => 'heroicon-o-home',
            ],
            [
                'label' => 'Antrian',
                'url' => url('/app/antrian-masuk'),
                'active' => request()->is('app/antrian-masuk*'),
                'icon' => 'heroicon-o-inbox-arrow-down',
            ],
            [
                'label' => 'Proses',
                'url' => url('/app/unit-proses'),
                'active' => request()->is('app/unit-proses*'),
                'icon' => 'heroicon-o-cpu-chip',
            ],
        ];

        return view('livewire.mobile-bottom-nav', [
            'navItems' => $navItems,
        ]);
    }
}
