<?php

namespace App\Filament\Admin\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\Payment;

class PaidWaterChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'paidWaterChart';

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
    protected static ?string $heading = 'Paid Water Chart';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Get paid water payments for the current year
        $payments = Payment::query()
            ->whereYear('created_at', now()->year)
            ->where('payment_status', Payment::STATUS_PAID)
            ->whereNotNull('water_bill')
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($payment) => $payment->created_at->format('M'));

        // Prepare data arrays
        $waterBills = array_fill_keys(array_map(fn($m) => date('M', mktime(0, 0, 0, $m, 1)), range(1, 12)), 0);
        $waterConsumption = array_fill_keys(array_map(fn($m) => date('M', mktime(0, 0, 0, $m, 1)), range(1, 12)), 0);

        // Fill in the actual data
        foreach ($payments as $month => $monthPayments) {
            $waterBills[$month] = $monthPayments->sum('water_bill');
            $waterConsumption[$month] = $monthPayments->sum('water_consumption');
        }

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Water Bill',
                    'data' => array_values($waterBills),
                    'type' => 'column',
                ],
                [
                    'name' => 'Water Consumption',
                    'data' => array_values($waterConsumption),
                    'type' => 'line',
                ],
            ],
            'stroke' => [
                'width' => [0, 4],
            ],
            'xaxis' => [
                'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
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
            'legend' => [
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
            ],
        ];
    }
}
