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
use Illuminate\Support\Facades\DB;
use App\Models\Payment;
use EightyNine\Reports\Components\PageBreak;

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
        $concourse = Concourse::first();

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

        // Get all concourses if none selected in filter
        $concourses = Concourse::all();

        foreach ($concourses as $concourse) {
            // Add page break before each concourse except the first one
            if (!empty($schema)) {
                $schema[] = VerticalSpace::make();
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
                ->data(fn() => $this->spaceSummary([], $concourse->id));

            // Space Status Summary
            $schema[] = Text::make('Space Status Summary')
                ->subtitle();

            $schema[] = Body\Table::make()
                ->data(fn() => $this->spaceStatusSummary(['concourse_id' => $concourse->id]));


            $schema[] = VerticalSpace::make();
        }

        return $body
            ->schema([
                Body\Layout\BodyColumn::make()
                    ->schema($schema),
            ]);
    }

    private function getSpaces(): Collection
    {
        return Space::query()
            ->select('name', 'status', DB::raw('price / 100 as price'))
            ->where('concourse_id', Concourse::first()->id)
            ->get();
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
                \Filament\Forms\Components\Select::make('concourse_ids')
                    ->label('Concourses')
                    ->multiple()
                    ->options(Concourse::pluck('name', 'id'))
                    ->searchable()
                    ->native(false)
                    ->preload()
                    ->required(),
            ]);
    }

    public function spaceSummary(?array $filters, $concourseId): Collection
    {
        $spaces = Space::with(['payments.tenant'])
            ->where('concourse_id', $concourseId)
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
                // Get the latest payment for tenant name
                $latestPayment = Payment::where('space_id', $space->id)
                    ->with('tenant')
                    ->latest()
                    ->first();

                // Count delayed payments
                $delayedPaymentsCount = Payment::where('space_id', $space->id)
                    ->whereNotNull('due_date')
                    ->whereNotNull('paid_date')
                    ->whereRaw('paid_date > due_date')
                    ->count();

                // Calculate total paid delayed amount
                $paidDelayedAmount = Payment::where('space_id', $space->id)
                    ->whereNotNull('due_date')
                    ->whereNotNull('paid_date')
                    ->whereRaw('paid_date > due_date')
                    ->where('payment_status', Payment::STATUS_PAID)
                    ->sum(DB::raw('COALESCE(water_bill, 0) + COALESCE(electricity_bill, 0) + COALESCE(rent_bill, 0)'));

                // Calculate total unpaid delayed amount from space dues
                $unpaidDelayedAmount = 0;
                if ($space->water_payment_status == 'unpaid' && $space->water_due < now()) {
                    $unpaidDelayedAmount += $space->water_bills ?? 0;
                }
                if ($space->electricity_payment_status == 'unpaid' && $space->electricity_due < now()) {
                    $unpaidDelayedAmount += $space->electricity_bills ?? 0;
                }
                if ($space->rent_payment_status == 'unpaid' && $space->rent_due < now()) {
                    $unpaidDelayedAmount += $space->rent_bills ?? 0;
                }

                

                // Calculate total penalties
                $totalPenalties = $space->penalty;

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
                    'column10' => $totalPenalties,
                ];
            }));
    }

    public function spaceStatusSummary(?array $filters): Collection
    {
        $query = Space::query();

        if (isset($filters['concourse_id'])) {
            $query->where('concourse_id', $filters['concourse_id']);
        }

        $totalCount = $query->count();
        $availableCount = (clone $query)->where('status', 'available')->count();
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
