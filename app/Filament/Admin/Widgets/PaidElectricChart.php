<?php

namespace App\Filament\Admin\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\Payment;

class PaidElectricChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'paidElectricChart';

     /**
     * Sort
     */
    protected static ?int $sort = 7;

    /**
     * Widget content height
     */
    protected static ?int $contentHeight = 275;

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Paid Electric Chart';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Get paid electricity payments for the current year
        $payments = Payment::query()
            ->whereYear('created_at', now()->year)
            ->where('payment_status', Payment::STATUS_PAID)
            ->whereNotNull('electricity_bill')
            ->orderBy('created_at')
            ->get()
            ->groupBy(fn ($payment) => $payment->created_at->format('M'));

        // Prepare data arrays
        $electricityBills = array_fill_keys(array_map(fn($m) => date('M', mktime(0, 0, 0, $m, 1)), range(1, 12)), 0);
        $electricityConsumption = array_fill_keys(array_map(fn($m) => date('M', mktime(0, 0, 0, $m, 1)), range(1, 12)), 0);

        // Fill in the actual data
        foreach ($payments as $month => $monthPayments) {
            $electricityBills[$month] = $monthPayments->sum('electricity_bill');
            $electricityConsumption[$month] = $monthPayments->sum('electricity_consumption');
        }

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Electricity Bill',
                    'data' => array_values($electricityBills),
                    'type' => 'column',
                ],
                [
                    'name' => 'Electricity Consumption',
                    'data' => array_values($electricityConsumption),
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
