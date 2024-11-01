<?php

namespace App\Filament\Reports;

use EightyNine\Reports\Report;
use App\Models\Concourse;
use App\Models\Space;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\Text;
use EightyNine\Reports\Components\VerticalSpace;
use Filament\Forms\Form;
use Illuminate\Support\Collection;
use App\Models\Payment;

class ConcourseReport extends Report
{
    public ?string $heading = "Concourse Report";

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
                                Text::make('Concourse Report')
                                    ->title(),
                                Text::make('This report shows the status of the concourse')
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

        $concourse = isset($this->filters['concourse_id'])
            ? Concourse::find($this->filters['concourse_id'])
            : Concourse::where('is_active', true)->first();

        if (!$concourse) {
            return $body->schema([]);
        }

        // Concourse Header
        $schema[] = Text::make($concourse->name)
            ->title();

        $schema[] = Text::make($concourse->address)
            ->subtitle();

        $schema[] = VerticalSpace::make();

        // Detailed Space Summary
        $schema[] = Text::make('Detailed Space Summary')
            ->subtitle();

        $schema[] = Body\Table::make()
            ->data(fn() => $this->spaceSummary($concourse->id));

        $schema[] = VerticalSpace::make();

        // Space Status Summary
        $schema[] = Text::make('Space Status Summary')
            ->subtitle();

        $schema[] = Body\Table::make()
            ->data(fn() => $this->spaceStatusSummary($concourse->id));

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
                \Filament\Forms\Components\Select::make('concourse_id')
                    ->label('Concourse')
                    ->options(Concourse::where('is_active', true)->pluck('name', 'id'))
                    ->searchable()
                    ->nullable()
                    ->default(Concourse::where('is_active', true)->first()?->id)
            ]);
    }

    public function spaceSummary(int $concourseId): Collection
    {
        // Get all active spaces for this concourse with their related payments and user
        $spaces = Space::query()
            ->where('concourse_id', $concourseId)
            ->where('is_active', true)
            ->with(['payments' => function ($query) use ($concourseId) {
                $query->where('concourse_id', $concourseId)
                    ->whereNotNull('due_date')
                    ->whereNotNull('paid_date')
                    ->whereRaw('paid_date > due_date');
            }, 'user'])
            ->get();

        $headerRow = [
            'column1' => 'Space Name',
            'column2' => 'Tenant Name',
            'column3' => 'Business Type',
            'column4' => 'Business Name',
            'column5' => 'Number of Delayed Payments',
            'column6' => 'Total Paid Delayed Amount',
            'column7' => 'Total Unpaid Delayed Amount',
            'column8' => 'Lease Start',
            'column9' => 'Lease End',
            'column10' => 'Total Penalties',
        ];

        return collect([$headerRow])
            ->concat($spaces->map(function ($space) {
                // Get delayed payments for this space
                $delayedPayments = $space->payments()
                    ->whereNotNull('due_date')
                    ->whereNotNull('paid_date')
                    ->whereRaw('paid_date > due_date')
                    ->get();

                $delayedPaymentsCount = $delayedPayments->count();

                // Calculate total paid delayed amount
                $paidDelayedAmount = $delayedPayments
                    ->where('payment_status', Payment::STATUS_PAID)
                    ->sum(function ($payment) {
                        return ($payment->water_bill ?? 0) +
                            ($payment->electricity_bill ?? 0) +
                            ($payment->rent_bill ?? 0);
                    });

                // Calculate unpaid delayed amount
                $unpaidDelayedAmount = 0;
                if ($space->water_payment_status == 'unpaid' && $space->water_due && $space->water_due < now()) {
                    $unpaidDelayedAmount += $space->water_bills ?? 0;
                }
                if ($space->electricity_payment_status == 'unpaid' && $space->electricity_due && $space->electricity_due < now()) {
                    $unpaidDelayedAmount += $space->electricity_bills ?? 0;
                }
                if ($space->rent_payment_status == 'unpaid' && $space->rent_due && $space->rent_due < now()) {
                    $unpaidDelayedAmount += $space->rent_bills ?? 0;
                }

                return [
                    'column1' => $space->name,
                    'column2' => $space->user?->name ?? 'N/A',
                    'column3' => $space->business_type ?? 'N/A',
                    'column4' => $space->business_name ?? 'N/A',
                    'column5' => $delayedPaymentsCount,
                    'column6' => number_format($paidDelayedAmount, 2),
                    'column7' => number_format($unpaidDelayedAmount, 2),
                    'column8' => $space->lease_start ? $space->lease_start->format('F d, Y') : 'N/A',
                    'column9' => $space->lease_end ? $space->lease_end->format('F d, Y') : 'N/A',
                    'column10' => number_format($space->penalty ?? 0, 2),
                ];
            }));
    }

    public function spaceStatusSummary(int $concourseId): Collection
    {
        // Get spaces for this concourse only
        $spaces = Space::where('concourse_id', $concourseId)
            ->where('is_active', true);

        $totalCount = $spaces->count();
        $availableCount = (clone $spaces)->where('status', 'available')->count();
        $occupiedCount = $totalCount - $availableCount;

        return collect([
            [
                'column1' => 'Status',
                'column2' => 'Count',
            ],
            [
                'column1' => 'Available',
                'column2' => $availableCount,
            ],
            [
                'column1' => 'Occupied',
                'column2' => $occupiedCount,
            ],
            [
                'column1' => 'Total',
                'column2' => $totalCount,
            ],
        ]);
    }
}
