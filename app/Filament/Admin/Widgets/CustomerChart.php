<?php

namespace App\Filament\Admin\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class CustomerChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'customerChart';

    /**
     * Sort
     */
    protected static ?int $sort = 2;

    /**
     * Widget content height
     */
    protected static ?int $contentHeight = 270;

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Customer Chart';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */

     protected function getOptions(): array
    {
        $customerData = $this->getCustomerData();

        return [
            'chart' => [
                'type' => 'line',
                'height' => 250,
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'series' => [
                [
                    'name' => 'Customers',
                    'data' => $customerData['counts'],
                ],
            ],
            'xaxis' => [
                'categories' => $customerData['months'],
                'labels' => [
                    'style' => [
                        'fontWeight' => 400,
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                'labels' => [
                    'style' => [
                        'fontWeight' => 400,
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'fill' => [
                'type' => 'gradient',
                'gradient' => [
                    'shade' => 'dark',
                    'type' => 'horizontal',
                    'shadeIntensity' => 1,
                    'gradientToColors' => ['#ea580c'],
                    'inverseColors' => true,
                    'opacityFrom' => 1,
                    'opacityTo' => 1,
                    'stops' => [0, 100, 100, 100],
                ],
            ],

            'dataLabels' => [
                'enabled' => false,
            ],
            'grid' => [
                'show' => false,
            ],
            'markers' => [
                'size' => 2,
            ],
            'tooltip' => [
                'enabled' => true,
            ],
            'stroke' => [
                'width' => 4,
            ],
            'colors' => ['#f59e0b'],
        ];
    }

    private function getCustomerData(): array
    {
        $data = \App\Models\User::selectRaw('COUNT(*) as count, DATE_FORMAT(created_at, "%b") as month, MONTH(created_at) as month_num')
            ->whereYear('created_at', now()->year)
            ->groupBy('month', 'month_num')
            ->orderBy('month_num')
            ->get();

        $months = [];
        $counts = [];

        foreach ($data as $item) {
            $months[] = $item->month;
            $counts[] = $item->count;
        }

        return [
            'months' => $months,
            'counts' => $counts,
        ];
    }
}
