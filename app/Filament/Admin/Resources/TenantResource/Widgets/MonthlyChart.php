<?php

namespace App\Filament\Admin\Resources\TenantResource\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class MonthlyChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'monthlyChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Monthly Chart';

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    public ?int $tenantId = null;

    protected function getOptions(): array
    {
        $data = $this->getMonthlyPaymentData();

        return [
            'chart' => [
                'type' => 'line',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Monthly Payments',
                    'data' => $data['amounts'],
                    'type' => 'column',
                ],
            ],
            'stroke' => [
                'width' => [0, 4],
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
            'legend' => [
                'labels' => [
                    'fontFamily' => 'inherit',
                ],
            ],
        ];
    }

    protected function getMonthlyPaymentData(): array
    {
        $payments = Payment::query()
            ->when($this->tenantId, fn (Builder $query) => $query->where('tenant_id', $this->tenantId))
            ->whereYear('created_at', Carbon::now()->year)
            ->get()
            ->groupBy(fn ($payment) => $payment->created_at->format('M'))
            ->map(fn ($group) => $group->sum('amount'));

        $months = collect(range(1, 12))->map(fn ($month) => Carbon::create(null, $month, 1)->format('M'));

        $amounts = $months->map(fn ($month) => $payments->get($month, 0));

        return [
            'months' => $months->values()->all(),
            'amounts' => $amounts->values()->all(),
        ];
    }
}
