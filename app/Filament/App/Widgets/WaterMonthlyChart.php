<?php

namespace App\Filament\App\Widgets;

use App\Models\Payment;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Illuminate\Support\Facades\Auth;
use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use Illuminate\Support\Facades\DB;

class WaterMonthlyChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'waterMonthlyChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Water Monthly Chart';

     /**
     * Sort
     */
    protected static ?int $sort = 2;

     /**
     * Filter Form
     */
    protected function getFormSchema(): array
    {
        return [

            Radio::make('ordersChartType')
                ->default('bar')
                ->options([
                    'line' => 'Line',
                    'bar' => 'Col',
                    'area' => 'Area',
                ])
                ->inline(true)
                ->label('Type'),

            Grid::make()
                ->schema([
                    Toggle::make('ordersChartMarkers')
                        ->default(false)
                        ->label('Markers'),

                    Toggle::make('ordersChartGrid')
                        ->default(false)
                        ->label('Grid'),
                ]),

            TextInput::make('ordersChartAnnotations')
                ->required()
                ->numeric()
                ->default(7500)
                ->label('Annotations'),
        ];
    }

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     */
    protected function getOptions(): array
    {
        $filters = $this->filterFormData;

        $monthlyData = $this->getMonthlyWaterConsumption();

        return [
            'chart' => [
                'type' => $filters['ordersChartType'],
                'height' => 250,
                'toolbar' => [
                    'show' => false,
                ],
            ],
            'series' => [
                [
                    'name' => 'Water Consumption',
                    'data' => $monthlyData['consumption'],
                ],
            ],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 2,
                ],
            ],
            'xaxis' => [
                'categories' => $monthlyData['months'],
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
                    'type' => 'vertical',
                    'shadeIntensity' => 0.5,
                    'gradientToColors' => ['#fbbf24'],
                    'inverseColors' => true,
                    'opacityFrom' => 1,
                    'opacityTo' => 1,
                    'stops' => [0, 100],
                ],
            ],

            'dataLabels' => [
                'enabled' => false,
            ],
            'grid' => [
                'show' => $filters['ordersChartGrid'],
            ],
            'markers' => [
                'size' => $filters['ordersChartMarkers'] ? 3 : 0,
            ],
            'tooltip' => [
                'enabled' => true,
            ],
            'stroke' => [
                'width' => $filters['ordersChartType'] === 'line' ? 4 : 0,
            ],
            'colors' => ['#f59e0b'],
            'annotations' => [
                'yaxis' => [
                    [
                        'y' => $filters['ordersChartAnnotations'],
                        'borderColor' => '#ef4444',
                        'borderWidth' => 1,
                        'label' => [
                            'borderColor' => '#ef4444',
                            'style' => [
                                'color' => '#fffbeb',
                                'background' => '#ef4444',
                            ],
                            'text' => 'Annotation: ' . $filters['ordersChartAnnotations'],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * Get monthly water consumption data
     */
    protected function getMonthlyWaterConsumption(): array
    {
        $data = Payment::select(
            DB::raw('MONTH(created_at) as month'),
            DB::raw('SUM(water_consumption) as total_consumption')
        )
        ->whereYear('created_at', date('Y'))
        ->where('tenant_id', Auth::user()->id)
        ->groupBy('month')
        ->orderBy('month')
        ->get();

        $months = [];
        $consumption = array_fill(0, 12, 0);

        foreach ($data as $item) {
            $monthIndex = $item->month - 1;
            $months[$monthIndex] = date('M', mktime(0, 0, 0, $item->month, 1));
            $consumption[$monthIndex] = $item->total_consumption;
        }

        return [
            'months' => $months,
            'consumption' => $consumption,
        ];
    }
}
