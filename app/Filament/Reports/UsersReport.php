<?php

namespace App\Filament\Reports;

use App\Models\User;
use EightyNine\Reports\Report;
use EightyNine\Reports\Components\Body;
use EightyNine\Reports\Components\Footer;
use EightyNine\Reports\Components\Header;
use EightyNine\Reports\Components\Text;
use EightyNine\Reports\Components\VerticalSpace;
use Filament\Forms\Form;
use Illuminate\Support\Collection;

class UsersReport extends Report
{
    public ?string $heading = "Report";

    // public ?string $subHeading = "A great report";

    public function header(Header $header): Header
    {
        return $header
            ->schema([
                Header\Layout\HeaderRow::make()
                    ->schema([
                        Header\Layout\HeaderColumn::make()
                            ->schema([
                                Text::make('Users Report')
                                    ->title(),
                                Text::make('This report shows all users in the system')
                                    ->subtitle(),
                            ]),
                        Header\Layout\HeaderColumn::make()
                            ->schema([
                                Text::make(now()->format('F, d Y'))
                                    ->subtitle(),
                            ])->alignRight(),
                    ])
            ]);
    }


    public function body(Body $body): Body
    {
        return $body
            ->schema([
                Body\Layout\BodyColumn::make()
                    ->schema([
                        Body\Table::make()
                            ->data(
                                fn(?array $filters) => $this->registrationSummary($filters)
                            ),
                        VerticalSpace::make(),
                        Body\Table::make()
                            ->data(
                                fn(?array $filters) => $this->verificationSummary($filters)
                            ),
                    ]),
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
                \Filament\Forms\Components\TextInput::make('search')
                    ->placeholder('Search')
                    ->autofocus(),
                \Filament\Forms\Components\Select::make('status')
                    ->placeholder('Status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ]),
                \Filament\Forms\Components\DatePicker::make('created_at')
                    ->placeholder('Created At')
                    ->timezone('Asia/Manila')
                    ->displayFormat('F d, Y')
                    ->minDate(now()->subDays(30))
                    ->maxDate(now())
                    ->native(false),
            ]);
    }

    public function registrationSummary(?array $filters): Collection
    {
        $users = User::all();
        
        return collect([
            [
                'column1' => 'Date Created',
                'column2' => 'Full Name',
                'column3' => 'Email',
                'column4' => 'Status',
            ]
        ])->concat($users->map(function ($user) {
            return [
                'column1' => $user->created_at->format('Y-m-d'),
                'column2' => $user->first_name . ' ' . $user->last_name,
                'column3' => $user->email,
                'column4' => $user->email_verified_at ? 'Verified' : 'Not Verified',
            ];
        }));
    }

    public function verificationSummary(?array $filters): Collection
    {

        return collect([
            // ['column1' => 'Verification Data 1', 'column2' => 'Value 1'],

        ]);
    }
}
