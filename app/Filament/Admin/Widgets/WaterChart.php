<?php

namespace App\Filament\Admin\Widgets;

use App\Models\Payment;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
        $data = $this->getWaterBillData();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Water Bill',
                    'data' => $data['bills'],
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
            'colors' => ['#3b82f6'], // Changed to a blue color for water
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'horizontal' => false,
                ],
            ],
        ];
    }

    private function getWaterBillData(): array
    {
        $currentYear = date('Y');
        
        $billData = Payment::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(water_bill) as total_water')
        )
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->orderBy('month')
            ->get();

        $months = array_fill(0, 12, 0);
        $bills = array_fill(0, 12, 0);

        foreach ($billData as $data) {
            $monthIndex = $data->month - 1;
            $bills[$monthIndex] = round($data->total_water, 2);
        }

        // Fill in all months
        for ($i = 0; $i < 12; $i++) {
            $months[$i] = date('M', mktime(0, 0, 0, $i + 1, 1));
        }

        return [
            'months' => array_values($months),
            'bills' => array_values($bills),
        ];
    }
}
