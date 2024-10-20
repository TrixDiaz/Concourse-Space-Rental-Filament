<?php

namespace App\Filament\Admin\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Payment;
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
        $totalRevenue = Payment::where('payment_status', 'completed')->sum('amount');
        $newCustomers = Payment::distinct('tenant_id')->count();
        $newOrders = Payment::count();

        $revenueChart = $this->getChartData('amount');
        $customersChart = $this->getChartData('tenant_id', 'count');
        $ordersChart = $this->getChartData('id', 'count');

        return [
            Stat::make('Revenue', '₱' . number_format($totalRevenue, 2))
                ->description($this->getChangeDescription($revenueChart))
                ->descriptionIcon($this->getChangeIcon($revenueChart))
                ->chart($revenueChart)
                ->color($this->getChangeColor($revenueChart)),
            Stat::make('New customers', $newCustomers)
                ->description($this->getChangeDescription($customersChart))
                ->descriptionIcon($this->getChangeIcon($customersChart))
                ->chart($customersChart)
                ->color($this->getChangeColor($customersChart)),
            Stat::make('New orders', $newOrders)
                ->description($this->getChangeDescription($ordersChart))
                ->descriptionIcon($this->getChangeIcon($ordersChart))
                ->chart($ordersChart)
                ->color($this->getChangeColor($ordersChart)),
        ];
    }

    private function getChartData(string $column, string $aggregation = 'sum'): array
    {
        return Payment::where('payment_status', 'completed')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('created_at')
            ->pluck(DB::raw("$aggregation($column) as total"))
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
