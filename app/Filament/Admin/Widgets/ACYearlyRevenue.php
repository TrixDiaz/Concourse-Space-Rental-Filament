<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ACYearlyRevenue extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'aCYearlyRevenue';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Yearly Revenue';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Get payment data dynamically from the database
        $paymentData = Payment::selectRaw('COUNT(*) as count, payment_method')
            ->groupBy('payment_method')
            ->whereIn('payment_method', ['maya', 'gcash']) // Adjust the payment options as needed
            ->get();

        $datasets = $paymentData->pluck('count')->toArray();
        $labels = $paymentData->pluck('payment_method')->toArray();

        // Now merge the datasets and labels with the rest of the chart options
        return [
            'chart' => [
                'type' => 'donut',
                'height' => 300,
            ],
            'series' => $datasets,
            'labels' => $labels,
            'legend' => [
                'labels' => [
                    'fontFamily' => 'poppins',
                ],
            ],
        ];
    }
}
