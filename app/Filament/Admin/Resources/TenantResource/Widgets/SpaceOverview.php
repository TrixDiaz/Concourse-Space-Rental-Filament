<?php

namespace App\Filament\Admin\Resources\TenantResource\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SpaceOverview extends BaseWidget
{
    protected function getStats(): array
    {
        if (!$this->record) {
            return [];
        }

        $pendingMonthlyPayment = $this->record->monthly_payment;
        $totalPaymentPerYear = $this->record->monthly_payment * 12;
        $concourseRate = $this->record->concourse->concourseRate->price ?? 0;
        $concourseName = $this->record->concourse->concourseRate->name ?? '';
      

        return [
            Stat::make('Pending Monthly Payment', '₱' . number_format($pendingMonthlyPayment, 2))
                ->description('Due this month')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('warning'),
            Stat::make('Total Payment per Year', '₱' . number_format($totalPaymentPerYear, 2))
                ->description('Annual total')
                ->descriptionIcon('heroicon-m-calendar')
                ->color('info'),
            Stat::make('Concourse Rate', '₱' . number_format($concourseRate, 2))
                ->description($concourseName)
                ->descriptionIcon('heroicon-m-building-office')
                ->color('success'),
        ];
    }
}
