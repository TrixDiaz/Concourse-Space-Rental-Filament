<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class BillsOverview extends BaseWidget
{
    public $concourse;

    public function mount($concourse)
    {
        $this->concourse = $concourse;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Water Bills', $this->concourse->water_bills)
            ->description('Total water bills')
            ->descriptionIcon('heroicon-m-beaker')
            ->color('warning'),
            
            Stat::make('Electricity Bills', $this->concourse->electricity_bills)
            ->description('Total electricity bills')
            ->descriptionIcon('heroicon-m-bolt')
            ->color('danger'),
        ];
    }
}
