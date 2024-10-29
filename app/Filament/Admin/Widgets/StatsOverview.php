<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Payment;
use App\Models\Application;
use App\Models\User;
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
        $totalRevenue = Payment::where('payment_status', 'paid')->sum('amount');
        
        // Water stats
        $totalWaterBill = Payment::where('payment_status', 'paid')->sum('water_bill');
        $totalWaterConsumption = Payment::sum('water_consumption');
        
        // Electric stats
        $totalElectricBill = Payment::where('payment_status', 'paid')->sum('electricity_bill');
        $totalElectricConsumption = Payment::sum('electricity_consumption');

        $revenueChart = $this->getChartData(Payment::class, 'amount');
        $waterBillChart = $this->getChartData(Payment::class, 'water_bill');
        $electricBillChart = $this->getChartData(Payment::class, 'electricity_bill');

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
