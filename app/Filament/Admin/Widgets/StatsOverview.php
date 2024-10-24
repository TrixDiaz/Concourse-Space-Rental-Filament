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
        $newCustomers = User::role('panel_user')->count();
        $newApplications = Application::count();

        $revenueChart = $this->getChartData(Payment::class, 'amount');
        $customersChart = $this->getChartData(User::class, 'id', 'count');
        $applicationsChart = $this->getChartData(Application::class, 'id', 'count');

        return [
            Stat::make('Revenue', '₱' . number_format($totalRevenue, 2))
                ->description($this->getChangeDescription($revenueChart))
                ->descriptionIcon($this->getChangeIcon($revenueChart))
                ->chart($revenueChart)
                ->color($this->getChangeColor($revenueChart)),
            Stat::make('New Users', $newCustomers)
                ->description($this->getChangeDescription($customersChart))
                ->descriptionIcon($this->getChangeIcon($customersChart))
                ->chart($customersChart)
                ->color($this->getChangeColor($customersChart)),
            Stat::make('New Applications', $newApplications)
                ->description($this->getChangeDescription($applicationsChart))
                ->descriptionIcon($this->getChangeIcon($applicationsChart))
                ->chart($applicationsChart)
                ->color($this->getChangeColor($applicationsChart)),
        ];
    }

    private function getChartData(string $model, string $column, string $aggregation = 'sum'): array
    {
        $query = $model::query();
        
        if ($model === Payment::class) {
            $query->where('payment_status', 'paid');
        } elseif ($model === User::class) {
            $query->role('panel_user');
        }

        return $query->where('created_at', '>=', now()->subDays(7))
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
