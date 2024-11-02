<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Space;
use Illuminate\Support\Facades\DB;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;

class ElectricChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'electricChart';

    /**
     * Sort
     */
    protected static ?int $sort = 5;

    /**
     * Widget content height
     */
    protected static ?int $contentHeight = 275;

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Electric Monthly Chart';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $data = $this->getBillData();

        return [
            'chart' => [
                'type' => 'line',  // Changed to support multiple types
                'height' => 300,
                'toolbar' => ['show' => false],
            ],
            'series' => [
                [
                    'name' => 'Electric Bill',
                    'type' => 'bar',
                    'data' => $data['electric'],
                ],
                [
                    'name' => 'Consumption (kWh)',
                    'type' => 'line',
                    'data' => $data['consumption'],
                ],
            ],
            'xaxis' => [
                'categories' => $data['months'],
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'yaxis' => [
                [
                    'title' => [
                        'text' => 'Electric Bill',
                    ],
                    'labels' => [
                        'style' => [
                            'fontFamily' => 'inherit',
                        ],
                    ],
                ],
                [
                    'opposite' => true,
                    'title' => [
                        'text' => 'Consumption (kWh)',
                    ],
                    'labels' => [
                        'style' => [
                            'fontFamily' => 'inherit',
                        ],
                    ],
                ],
            ],
            'colors' => ['#f59e0b', '#3b82f6'], // Yellow for bill, Blue for consumption
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'horizontal' => false,
                ],
            ],
        ];
    }

    protected function getBillData(): array
    {
        $currentYear = date('Y');
        
        $billData = Space::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(electricity_bills) as total_electric'),
            DB::raw('SUM(electricity_consumption) as total_consumption')
        )   
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = array_fill(0, 12, 0);
        $electric = array_fill(0, 12, 0);
        $consumption = array_fill(0, 12, 0);  // Add this line

        foreach ($billData as $data) {
            $monthIndex = $data->month - 1;
            $electric[$monthIndex] = round($data->total_electric, 2);
            $consumption[$monthIndex] = round($data->total_consumption, 2);  // Add this line
        }

        // Fill in all months
        for ($i = 0; $i < 12; $i++) {
            $months[$i] = date('M', mktime(0, 0, 0, $i + 1, 1));
        }

        return [
            'months' => array_values($months),
            'electric' => array_values($electric),
            'consumption' => array_values($consumption),  // Add this line
        ];
    }

    public function exportData()
    {
        $data = $this->getBillData();
        
        $csvContent = "Month,Electric,Consumption\n";
        foreach ($data['months'] as $index => $month) {
            $csvContent .= "{$month},{$data['electric'][$index]},{$data['consumption'][$index]}\n";
        }

        $fileName = 'monthly_electric_payments_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
