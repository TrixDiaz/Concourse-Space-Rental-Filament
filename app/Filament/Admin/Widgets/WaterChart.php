<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Facades\Auth;

class WaterChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'waterChart';

     /**
     * Sort
     */
    protected static ?int $sort = 6;

    /**
     * Widget content height
     */
    protected static ?int $contentHeight = 275;

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Water Monthly Payments Chart';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $tenantId = Auth::id(); // Get the current user's ID
        $waterBillsByMonth = $this->getWaterBillsByMonth($tenantId);

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Water Bill',
                    'data' => $waterBillsByMonth['bills'],
                ],
            ],
            'xaxis' => [
                'categories' => $waterBillsByMonth['months'],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#3b82f6'], // Changed to a blue color for water
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'horizontal' => false,
                ],
            ],
        ];
    }

    private function getWaterBillsByMonth($tenantId): array
    {
        $payments = Payment::where('tenant_id', $tenantId)
            ->whereNotNull('water_bill')
            ->whereYear('created_at', now()->year)
            ->orderBy('created_at')
            ->get();

        $months = [];
        $bills = [];

        foreach ($payments as $payment) {
            $month = $payment->created_at->format('M');
            $months[] = $month;
            $bills[] = $payment->water_bill;
        }

        return [
            'months' => $months,
            'bills' => $bills,
        ];
    }
}
