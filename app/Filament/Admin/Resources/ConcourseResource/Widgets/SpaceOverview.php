<?php

namespace App\Filament\Admin\Resources\ConcourseResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SpaceOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Spaces', '100'),
            Stat::make('Available Spaces', '21'),
            Stat::make('Occupied Spaces', '12'),
            Stat::make('Under Maintenance Spaces', '3'),
        ];
    }
}
