<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ABMonthlyRevenue extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'aBMonthlyRevenue';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Monthly Revenue';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        // Get the monthly data dynamically from the database
        $monthlyData = Payment::selectRaw('SUM(amount) as total_income, MONTH(created_at) as month')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        // Sort data by month to ensure labels are in the correct order
        $monthlyData = $monthlyData->sortBy('month');

        $datasets = [
            [
                'name' => 'Earning',
                'data' => $monthlyData->pluck('total_income')->toArray(),
                'type' => 'column',
            ],
            // If you have expenses data, you can include another dataset here
        ];

        $labels = $monthlyData->pluck('month')->map(function ($month) {
            return date('M', mktime(0, 0, 0, $month, 1));
        })->toArray();

        // Ensure the labels start from January regardless of the data
        $orderedLabels = [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ];

        $labels = array_intersect($orderedLabels, $labels);

         // Now merge the datasets and labels with the rest of the chart options
         return [
            'chart' => [
                'type' => 'bar', // Assuming you want a bar chart
                'height' => 300,
            ],
            'series' => $datasets,
            'xaxis' => [
                'categories' => $labels,
                'labels' => [
                    'style' => [
                        'fontFamily' => 'poppins',
                    ],
                ],
            ],
            // ... [rest of the options]
        ];
    }
}
