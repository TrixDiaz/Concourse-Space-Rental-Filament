<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Payment;
use App\Models\Space;
use Illuminate\Support\Facades\DB;

class StatsOverview extends BaseWidget
{
    /**
     * Sort
     */
    protected static ?int $sort = 1;

    /**
     * Widget content height
     */
    protected static ?int $contentHeight = 270;


    protected function getStats(): array
    {
        $totalRevenue = Payment::where('payment_status', 'paid')->sum('rent_bill');
        
        // Water stats from Spaces
        $totalWaterBill = Space::sum('water_bills');
        $totalWaterConsumption = Space::sum('water_consumption');
        
        // Electric stats from Spaces
        $totalElectricBill = Space::sum('electricity_bills');
        $totalElectricConsumption = Space::sum('electricity_consumption');

        $revenueChart = $this->getChartData(Payment::class, 'rent_bill');
        $waterBillChart = $this->getSpaceChartData('water_bills');
        $electricBillChart = $this->getSpaceChartData('electricity_bills');

        return [
            Stat::make('Revenue', '₱' . number_format($totalRevenue, 2))
                ->description($this->getChangeDescription($revenueChart))
                ->descriptionIcon($this->getChangeIcon($revenueChart))
                ->chart($revenueChart)
                ->color($this->getChangeColor($revenueChart)),
            Stat::make('Water Usage & Bills', number_format($totalWaterConsumption) . ' m³')
                ->description('₱' . number_format($totalWaterBill, 2) . ' total water bills')
                ->descriptionIcon($this->getChangeIcon($waterBillChart))
                ->chart($waterBillChart)
                ->color($this->getChangeColor($waterBillChart)),
            Stat::make('Electric Usage & Bills', number_format($totalElectricConsumption) . ' kWh')
                ->description('₱' . number_format($totalElectricBill, 2) . ' total electric bills')
                ->descriptionIcon($this->getChangeIcon($electricBillChart))
                ->chart($electricBillChart)
                ->color($this->getChangeColor($electricBillChart)),
        ];
    }

    private function getChartData(string $model, string $column, string $aggregation = 'sum'): array
    {
        return Payment::query()
            ->where('payment_status', 'paid')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('created_at')
            ->pluck(DB::raw("sum($column) as total"))
            ->toArray();
    }

    private function getSpaceChartData(string $column): array
    {
        return Space::query()
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('created_at')
            ->pluck(DB::raw("sum($column) as total"))
            ->toArray();
    }

    private function getChangeDescription(array $chartData): string
    {
        $change = $this->calculateChange($chartData);
        return abs($change) . '% ' . ($change >= 0 ? 'increase' : 'decrease');
    }

    private function getChangeIcon(array $chartData): string
    {
        $change = $this->calculateChange($chartData);
        return $change >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down';
    }

    private function getChangeColor(array $chartData): string
    {
        $change = $this->calculateChange($chartData);
        return $change >= 0 ? 'success' : 'danger';
    }

    private function calculateChange(array $chartData): float
    {
        if (count($chartData) < 2) {
            return 0;
        }

        $oldValue = $chartData[0];
        $newValue = end($chartData);

        if ($oldValue == 0) {
            return $newValue > 0 ? 100 : 0;
        }

        return round((($newValue - $oldValue) / $oldValue) * 100, 1);
    }
}
