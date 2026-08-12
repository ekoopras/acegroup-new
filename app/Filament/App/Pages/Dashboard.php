<?php

namespace App\Filament\App\Pages;

use App\Filament\App\Widgets\QuickMenuWidget;
use App\Filament\App\Widgets\ServiceStatsWidget;
use Filament\Pages\Page;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.app.pages.dashboard';

    public function getHeading(): string|Htmlable
    {
        return ''; // Menghilangkan teks "Dashboard"
    }

    protected function getHeaderWidgets(): array
    {
        return [
            ServiceStatsWidget::class,
            QuickMenuWidget::class,
        ];
    }
}
