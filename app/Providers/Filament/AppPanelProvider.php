<?php

namespace App\Providers\Filament;

use App\Filament\App\Pages\Dashboard;
use App\Filament\App\Widgets\ServiceStatsWidget;
use Filament\Facades\Filament;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Support\Facades\Blade;

class AppPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('app')
            ->path('app')
            ->darkMode(false)
            ->renderHook(
                PanelsRenderHook::TOPBAR_START,
                fn(): string => Blade::render('
                <div class="flex items-center gap-x-3 md:hidden pl-2">
                    <img src="' . asset('ico.png') . '" alt="Logo" class="h-10 w-auto">
                    <span class="text-md font-bold tracking-tight text-gray-900 dark:text-white">
                        ACEGROUP APP
                    </span>
                </div>
            '),
            )

            ->renderHook(
                'panels::head.end',
                fn(): string => '
        <!-- PWA -->
        <meta name="theme-color" content="#ffffff" media="(prefers-color-scheme: light)">
        <meta name="theme-color" content="#000000" media="(prefers-color-scheme: dark)">
        <link rel="apple-touch-icon" href="' . asset('ico.png') . '">
        <link rel="manifest" href="' . url('/android/manifest.json') . '">

        <!-- CSS Menyembunyikan Navigasi Bawaan Filament di Mobile -->
        <style>
            @media (max-width: 1023px) {
                /* Sembunyikan tombol hamburger / sidebar toggle */
                .fi-topbar .fi-topbar-nav-toggle-btn,
                .fi-topbar button[x-on\:click*="sidebar"] {
                    display: none !important;
                }
            }
        </style>

        <script>
            if ("serviceWorker" in navigator) {
                navigator.serviceWorker.register("/sw.js", { scope: "/app/" })
                .then(function(reg) {
                    console.log("SW registered:", reg);
                }).catch(function(err) {
                    console.log("SW failed:", err);
                });
            }
        </script>
    '
            )
            ->brandName('ACEGROUP')
            ->sidebarWidth('13rem')
            ->topNavigation()
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->discoverResources(in: app_path('Filament/App/Resources'), for: 'App\\Filament\\App\\Resources')
            ->discoverPages(in: app_path('Filament/App/Pages'), for: 'App\\Filament\\App\\Pages')
            ->pages([
                //Pages\Dashboard::class,
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/App/Widgets'), for: 'App\\Filament\\App\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                ServiceStatsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn(): string => Blade::render('@livewire(\'mobile-bottom-nav\')'),
        );
    }
}
