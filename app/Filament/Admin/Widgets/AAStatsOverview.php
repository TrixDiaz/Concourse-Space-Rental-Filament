<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class AAStatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        $totalRevenueToday = DB::table('payments')
            ->whereDate('created_at', today())
            ->sum('amount');

        $totalRevenueThisYear = DB::table('payments')
            ->whereYear('created_at', date('Y'))
            ->sum('amount');

        $averageRevenueThisYear = DB::table('payments')
            ->whereYear('created_at', date('Y'))
            ->avg('amount');

        return [
            Stat::make('Total Revenue Today', number_format($totalRevenueToday, 2))
                ->description('Today\'s revenue')
                ->descriptionIcon('heroicon-m-information-circle')
                ->color('success'),
            Stat::make('Total Revenue This Year', number_format($totalRevenueThisYear, 2))
                ->description('Yearly revenue')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('info'),
            Stat::make('Average Revenue This Year', number_format($averageRevenueThisYear, 2))
                ->description('Yearly average')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color('warning'),
        ];
    }
}
