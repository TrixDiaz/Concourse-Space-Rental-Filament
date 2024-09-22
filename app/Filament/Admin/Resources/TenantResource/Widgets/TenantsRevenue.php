<?php

namespace App\Filament\Admin\Resources\TenantResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TenantsRevenue extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Revenue', '₱192.1k'),
            Stat::make('Total Expenses', '₱192.1k'),
            Stat::make('Net Income', '₱192.1k'),
        ];
    }
}
