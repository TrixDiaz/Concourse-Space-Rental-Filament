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
    protected static ?string $heading = 'Payment Platforms';

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
            ->whereIn('payment_method', ['cash', 'gcash']) 
            ->get();

        $datasets = $paymentData->pluck('count')->toArray();
        $labels = $paymentData->pluck('payment_method')->toArray();
        
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
