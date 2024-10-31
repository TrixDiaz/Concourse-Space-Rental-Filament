<?php

namespace App\Filament\Reports;

use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\Text;
use EightyNine\Reports\Components\VerticalSpace;
use Filament\Forms\Form;
use Illuminate\Support\Collection;
use App\Models\Space;
use App\Models\Payment;

class TenantReport extends Report
{
    public ?string $heading = "Tenant Report";

    protected array $filters = [];

    public function filterFormSubmitted(array $data): void
    {
        $this->filters = $data;
    }

    public function header(Header $header): Header
    {
        return $header
            ->schema([
                Header\Layout\HeaderRow::make()
                    ->schema([
                        Header\Layout\HeaderColumn::make()
                            ->schema([
                                Text::make('Tenant Report')
                                    ->title(),
                                Text::make('This report shows the detailed analysis of tenant payments')
                                    ->subtitle(),
                            ]),
                        Header\Layout\HeaderColumn::make()
                            ->schema([
                                Text::make(now()->format('F d, Y'))
                                    ->subtitle(),
                            ])->alignRight(),
                    ])
            ]);
    }

    public function body(Body $body): Body
    {
        $schema = [];

        $query = Space::query();

        if (!empty($this->filters['space_id'])) {
            $query->where('id', $this->filters['space_id']);
        } else {
            $query->first();
        }

        $spaces = !empty($this->filters['space_id']) ? $query->get() : collect([$query->first()]);

        if ($spaces->isEmpty()) {
            return $body->schema([]);
        }

        foreach ($spaces as $space) {
            if (!empty($schema)) {
                $schema[] = VerticalSpace::make();
            }

            // Space Header
            $schema[] = Text::make($space->business_name)
                ->title();

            $schema[] = Text::make("Space: {$space->name}")
                ->subtitle();

            $schema[] = VerticalSpace::make();

            // Payment Details
            $schema[] = Text::make('Payment Details')
                ->subtitle();

            $schema[] = Body\Table::make()
                ->data(fn() => $this->paymentDetails(['space_id' => $space->id]));

            $schema[] = VerticalSpace::make();

            // Payment Metrics
            $schema[] = Text::make('Payment Metrics')
                ->subtitle();

            $schema[] = Body\Table::make()
                ->data(fn() => $this->paymentMetrics(['space_id' => $space->id]));

            $schema[] = VerticalSpace::make();
        }

        return $body
            ->schema([
                Body\Layout\BodyColumn::make()
                    ->schema($schema),
            ]);
    }

    public function footer(Footer $footer): Footer
    {
        return $footer
            ->schema([
                Footer\Layout\FooterRow::make()
                    ->schema([
                        Footer\Layout\FooterColumn::make()
                            ->schema([
                                Text::make("Coms")
                                    ->title()
                                    ->primary(),
                                Text::make("All Rights Reserved")
                                    ->subtitle(),
                            ]),
                        Footer\Layout\FooterColumn::make()
                            ->schema([
                                Text::make("Generated on: " . now()->format('F d, Y')),
                            ])
                            ->alignRight(),
                    ]),
            ]);
    }

    public function filterForm(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Select::make('space_id')
                    ->label('Space')
                    ->options(
                        Space::query()
                            ->where('is_active', true)
                            ->whereNotNull('business_name')
                            ->pluck('business_name', 'id')
                            ->map(fn($name) => (string) $name)
                    )
                    ->searchable()
                    ->required()
            ]);
    }

    // Helper methods for data collection
    private function paymentDetails(?array $filters): Collection
    {
        $query = Payment::query()
            ->with(['tenant', 'space'])
            ->where('space_id', $filters['space_id'] ?? null)
            ->take(5);

        $payments = $query->get();

        // Define header row
        $headerRow = [
            'column1' => 'Payment Date',
            'column2' => 'Due Date',
            'column3' => 'Payment Type',
            'column4' => 'Amount',
            'column5' => 'Status',
            'column6' => 'Days Delayed',
            'column7' => 'Penalty',
        ];

        return collect([$headerRow])
            ->concat($payments->map(function ($payment) {
                $daysDelayed = null;
                if ($payment->due_date && $payment->paid_date) {
                    $daysDelayed = $payment->paid_date->diffInDays($payment->due_date);
                }

                return [
                    'column1' => $payment->paid_date ? $payment->paid_date->format('F d, Y') : 'N/A',
                    'column2' => $payment->due_date ? $payment->due_date->format('F d, Y') : 'N/A',
                    'column3' => $payment->payment_type ?? 'N/A',
                    'column4' => number_format($payment->amount ?? 0, 2),
                    'column5' => $payment->payment_status ?? 'N/A',
                    'column6' => $daysDelayed ? "{$daysDelayed} days" : 'N/A',
                    'column7' => number_format($payment->penalty ?? 0, 2),
                ];
            }));
    }

    private function paymentMetrics(?array $filters): Collection
    {
        $query = Payment::query()
            ->where('space_id', $filters['space_id'] ?? null)
            ->take(5);

        $totalPayments = $query->count();
        $paidPayments = (clone $query)->where('payment_status', 'paid')->count();
        $totalAmount = (clone $query)->sum('amount');
        $totalPenalties = (clone $query)->sum('penalty');
        $delayedPayments = (clone $query)
            ->whereNotNull('due_date')
            ->whereNotNull('paid_date')
            ->whereRaw('paid_date > due_date')
            ->count();

        return collect([
            [
                'column1' => 'Metric',
                'column2' => 'Value',
            ],
            [
                'column1' => 'Total Payments',
                'column2' => $totalPayments,
            ],
            [
                'column1' => 'Paid Payments',
                'column2' => $paidPayments,
            ],
            [
                'column1' => 'Delayed Payments',
                'column2' => $delayedPayments,
            ],
            [
                'column1' => 'Total Amount',
                'column2' => '₱ ' . number_format($totalAmount, 2),
            ],
            [
                'column1' => 'Total Penalties',
                'column2' => '₱ ' . number_format($totalPenalties, 2),
            ],
        ]);
    }
}
