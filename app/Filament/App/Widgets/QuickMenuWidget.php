<?php

namespace App\Filament\App\Widgets;

use Filament\Widgets\Widget;

class QuickMenuWidget extends Widget
{
    protected static string $view = 'filament.app.widgets.quick-menu-widget';

    protected static ?int $sort = -2;

    // Mengatur agar widget memenuhi lebar grid
    protected int | string | array $columnSpan = 'full';
}
