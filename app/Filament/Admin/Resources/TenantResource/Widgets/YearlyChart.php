<?php

namespace App\Filament\Admin\Resources\TenantResource\Widgets;

use Leandrocfe\FilamentApexCharts\Widgets\ApexChartWidget;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class YearlyChart extends ApexChartWidget
{
    /**
     * Chart Id
     *
     * @var string
     */
    protected static ?string $chartId = 'yearlyChart';

    /**
     * Widget Title
     *
     * @var string|null
     */
    protected static ?string $heading = 'Yearly Chart';

    public ?int $tenantId = null;

    /**
     * Chart options (series, labels, types, size, animations...)
     * https://apexcharts.com/docs/options
     *
     * @return array
     */
    protected function getOptions(): array
    {
        $data = $this->getYearlyPaymentData();

        return [
            'chart' => [
                'type' => 'bar',
                'height' => 300,
            ],
            'series' => [
                [
                    'name' => 'Yearly Payments',
                    'data' => $data['amounts'],
                ],
            ],
            'xaxis' => [
                'categories' => $data['years'],
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
            'colors' => ['#f59e0b'],
            'plotOptions' => [
                'bar' => [
                    'borderRadius' => 3,
                    'horizontal' => true,
                ],
            ],
        ];
    }

    protected function getYearlyPaymentData(): array
    {
        $currentYear = Carbon::now()->year;
        $startYear = $currentYear - 4; // Get data for the last 5 years

        $payments = Payment::query()
            ->when($this->tenantId, fn (Builder $query) => $query->where('tenant_id', $this->tenantId))
            ->whereYear('created_at', '>=', $startYear)
            ->whereYear('created_at', '<=', $currentYear)
            ->get()
            ->groupBy(fn ($payment) => $payment->created_at->format('Y'))
            ->map(fn ($group) => $group->sum('amount'));

        $years = collect(range($startYear, $currentYear))->map(fn ($year) => (string) $year);

        $amounts = $years->map(fn ($year) => $payments->get($year, 0));

        return [
            'years' => $years->values()->all(),
            'amounts' => $amounts->values()->all(),
        ];
    }
}
