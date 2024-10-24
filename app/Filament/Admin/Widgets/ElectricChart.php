<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
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
    protected static ?string $heading = 'Electric Monthly Payments Chart';

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
                'type' => 'bar',
                'height' => 300,
                'toolbar' => ['show' => false],
            ],
            'series' => [
                [
                    'name' => 'Electric',
                    'data' => $data['electric'],
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
                'labels' => [
                    'style' => [
                        'fontFamily' => 'inherit',
                    ],
                ],
            ],
            'colors' => ['#f59e0b'], // Yellow color for electricity
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
        $billData = Payment::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(electricity_bill) as total_electric')
        )
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = [];
        $electric = array_fill(0, 12, 0);

        foreach ($billData as $data) {
            $monthIndex = $data->month - 1;
            $months[$monthIndex] = date('M', mktime(0, 0, 0, $data->month, 1));
            $electric[$monthIndex] = round($data->total_electric, 2);
        }

        // Fill in any missing months
        for ($i = 0; $i < 12; $i++) {
            if (!isset($months[$i])) {
                $months[$i] = date('M', mktime(0, 0, 0, $i + 1, 1));
            }
        }

        ksort($months);

        return [
            'months' => array_values($months),
            'electric' => $electric,
        ];
    }

    public function exportData()
    {
        $data = $this->getBillData();
        
        $csvContent = "Month,Electric\n";
        foreach ($data['months'] as $index => $month) {
            $csvContent .= "{$month},{$data['electric'][$index]}\n";
        }

        $fileName = 'monthly_electric_payments_' . date('Y-m-d') . '.csv';

        return response()->streamDownload(function () use ($csvContent) {
            echo $csvContent;
        }, $fileName, [
            'Content-Type' => 'text/csv',
        ]);
    }
}
