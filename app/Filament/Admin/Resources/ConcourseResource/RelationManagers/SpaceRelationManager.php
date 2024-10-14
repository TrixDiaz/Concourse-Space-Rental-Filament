<?php

namespace App\Filament\Admin\Resources\ConcourseResource\RelationManagers;

use App\Models\Space;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class SpaceRelationManager extends RelationManager
{
    protected static string $relationship = 'spaces';



    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Select::make('user_id')
                                ->relationship('user', 'name')
                                ->label('Tenant Name')
                                ->preload()
                                ->disabled(),
                            Forms\Components\DatePicker::make('lease_start')
                                ->label('Lease Start')
                                ->native(false)
                                ->disabled(),
                            Forms\Components\DatePicker::make('lease_due')
                                ->label('Lease Due')
                                ->native(false)
                                ->disabled(),
                            Forms\Components\Select::make('lease_term')
                                ->label('Lease Term')
                                ->native(false)
                                ->options([
                                    '3' => '3 months',
                                    '6' => '6 months',
                                    '12' => '1 year',
                                    '24' => '2 years',
                                    '36' => '3 years',
                                ])
                                ->disabled(),
                        ])->columns(2),
                    Forms\Components\Section::make('Bills Utility')->description('Add the utility bills for the tenant')->schema([
                        Forms\Components\Repeater::make('bills')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->label('Name')
                                        ->required(),
                                    Forms\Components\TextInput::make('amount')
                                        ->label('Amount')
                                        ->prefix('₱')
                                        ->numeric()
                                        ->required(),
                                ])
                            ])
                            ->defaultItems(2)
                            ->createItemButtonLabel('Add Bill')
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                                return $data;
                            })
                            ->afterStateHydrated(function (Forms\Components\Repeater $component, $state) {
                                if (empty($state)) {
                                    $component->state([
                                        ['name' => 'Water', 'amount' => 0],
                                        ['name' => 'Electricity', 'amount' => 0],
                                    ]);
                                }
                            })
                            ->columnSpanFull()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $total = collect($state)->sum('amount');
                                $set('monthly_payment', $total);
                                $set('payment_status', 'unpaid');
                            })
                    ])->columns(2),
                ])->columnSpan([
                    'sm' => 3,
                    'md' => 3,
                    'lg' => 2
                ]),
                Forms\Components\Grid::make(1)->schema([
                    Forms\Components\Section::make('Monthly Payment')->schema([
                        Forms\Components\TextInput::make('monthly_payment')
                            ->label('Monthly Payment')
                            ->prefix('₱')
                            ->numeric()
                            ->readOnly()
                            ->default(0),
                        Forms\Components\Select::make('lease_status')
                            ->label('Lease Status')
                            ->native(false)
                            ->options([
                                'paid' => 'Paid',
                                'unpaid' => 'Unpaid',
                                'overdue' => 'Overdue',
                                'pending' => 'Pending',
                            ]),
                        Forms\Components\Select::make('status')
                            ->label('Space Status')
                            ->native(false)
                            ->options([
                                'available' => 'Available',
                                'occupied' => 'Occupied',
                                'under_maintenance' => 'Under Maintenance',
                            ]),
                        Forms\Components\TextInput::make('payment_status')
                            ->label('Payment Status')
                            ->readOnly(),
                    ]),
                    Forms\Components\Section::make('Visibility')->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->onIcon('heroicon-s-eye')
                            ->offIcon('heroicon-s-eye-slash')
                            ->label('Visible')
                            ->default(true),
                    ]),
                    Forms\Components\Section::make()->schema([
                        Forms\Components\Placeholder::make('created_at')
                            ->label('Created at')
                            ->hiddenOn('create')
                            ->content(function (\Illuminate\Database\Eloquent\Model $record): String {
                                $category = Space::find($record->id);
                                $now = \Carbon\Carbon::now();

                                $diff = $category->created_at->diff($now);
                                if ($diff->y > 0) {
                                    return $diff->y . ' years ago';
                                } elseif ($diff->m > 0) {
                                    if ($diff->m == 1) {
                                        return '1 month ago';
                                    } else {
                                        return $diff->m . ' months ago';
                                    }
                                } elseif ($diff->d >= 7) {
                                    $weeks = floor($diff->d / 7);
                                    if ($weeks == 1) {
                                        return 'a week ago';
                                    } else {
                                        return $weeks . ' weeks ago';
                                    }
                                } elseif ($diff->d > 0) {
                                    if ($diff->d == 1) {
                                        return 'yesterday';
                                    } else {
                                        return $diff->d . ' days ago';
                                    }
                                } elseif ($diff->h > 0) {
                                    if ($diff->h == 1) {
                                        return '1 hour ago';
                                    } else {
                                        return $diff->h . ' hours ago';
                                    }
                                } elseif ($diff->i > 0) {
                                    if ($diff->i == 1) {
                                        return '1 minute ago';
                                    } else {
                                        return $diff->i . ' minutes ago';
                                    }
                                } elseif ($diff->s > 0) {
                                    if ($diff->s == 1) {
                                        return '1 second ago';
                                    } else {
                                        return $diff->s . ' seconds ago';
                                    }
                                } else {
                                    return 'just now';
                                }
                            }),
                        Forms\Components\Placeholder::make('updated_at')
                            ->label('Last modified at')
                            ->content(function (\Illuminate\Database\Eloquent\Model $record): String {
                                $category = Space::find($record->id);
                                $now = \Carbon\Carbon::now();

                                $diff = $category->updated_at->diff($now);
                                if ($diff->y > 0) {
                                    return $diff->y . ' years ago';
                                } elseif ($diff->m > 0) {
                                    if ($diff->m == 1) {
                                        return '1 month ago';
                                    } else {
                                        return $diff->m . ' months ago';
                                    }
                                } elseif ($diff->d >= 7) {
                                    $weeks = floor($diff->d / 7);
                                    if ($weeks == 1) {
                                        return 'a week ago';
                                    } else {
                                        return $weeks . ' weeks ago';
                                    }
                                } elseif ($diff->d > 0) {
                                    if ($diff->d == 1) {
                                        return 'yesterday';
                                    } else {
                                        return $diff->d . ' days ago';
                                    }
                                } elseif ($diff->h > 0) {
                                    if ($diff->h == 1) {
                                        return '1 hour ago';
                                    } else {
                                        return $diff->h . ' hours ago';
                                    }
                                } elseif ($diff->i > 0) {
                                    if ($diff->i == 1) {
                                        return '1 minute ago';
                                    } else {
                                        return $diff->i . ' minutes ago';
                                    }
                                } elseif ($diff->s > 0) {
                                    if ($diff->s == 1) {
                                        return '1 second ago';
                                    } else {
                                        return $diff->s . ' seconds ago';
                                    }
                                } else {
                                    return 'just now';
                                }
                            }),
                    ])->hiddenOn('create')
                ])->columnSpan([
                    'sm' => 3,
                    'md' => 3,
                    'lg' => 1
                ])
            ])->columns(3)
        ;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('user_id')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Tenant')
                    ->default(fn($record) => $record->user->name ?? 'No Tenant')
                    ->description(fn($record) => $record->name)
                    ->extraAttributes(['class' => 'capitalize'])
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Price')
                    ->numeric()
                    ->sortable()
                    ->prefix('₱')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('sqm')
                    ->label('Sqm')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('lease_due')
                    ->label('Monthly Due')
                    ->date('F j, Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('Lease Term')
                    ->label('Lease Term')
                    ->default(fn($record) => \Carbon\Carbon::parse($record->lease_start)->addMonths($record->lease_term)->format('F j, Y'))
                    ->description(fn($record) => $record->lease_term . ' Months')
                    ->sortable(),
                Tables\Columns\TextColumn::make('Monthly Payment')
                    ->label('Monthly Payment')
                    ->default(fn($record) => $record->monthly_payment . ' ' . $record->payment_status)
                    ->extraAttributes(['class' => 'capitalize'])
                    ->prefix('₱')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->extraAttributes(['class' => 'capitalize']),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Visible in Tenant')
                    ->boolean()
                    ->extraAttributes(['class' => 'capitalize']),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('status')
                    ->options([
                        'available' => 'Available',
                        'occupied' => 'Occupied',
                        'under_maintenance' => 'Under Maintenance',
                    ]),
                SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ]),
            ])
            ->headerActions([
                // Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->color('info'),
                    Tables\Actions\Action::make('updateBills')
                        ->label('Update Monthly Rent')
                        ->icon('heroicon-m-currency-dollar')
                        ->color('primary')
                        ->requiresConfirmation()
                        ->action(function (Space $record) {
                            if ($record->lease_due) {
                                $leaseDate = \Carbon\Carbon::parse($record->lease_due);
                                $today = \Carbon\Carbon::today();

                                if ($leaseDate->lte($today)) {
                                    $rentAmount = $record->price ?? 0;
                                    $bills = $record->bills ?? [];

                                    // Check if the Monthly Rent bill already exists
                                    $billExists = collect($bills)->contains(function ($bill) use ($leaseDate) {
                                        return isset($bill['name']) && $bill['name'] == 'Monthly Rent' &&
                                            isset($bill['for_month']) && $bill['for_month'] == $leaseDate->format('Y-m');
                                    });

                                    if (!$billExists) {
                                        // Add Monthly Rent bill
                                        $bills[] = [
                                            'name' => 'Monthly Rent',
                                            'amount' => $rentAmount,
                                            'for_month' => $leaseDate->format('Y-m'),
                                        ];
                                    } else {
                                        // Update existing Monthly Rent bill
                                        $bills = collect($bills)->map(function ($bill) use ($rentAmount, $leaseDate) {
                                            if ($bill['name'] == 'Monthly Rent' && $bill['for_month'] == $leaseDate->format('Y-m')) {
                                                $bill['amount'] = $rentAmount;
                                            }
                                            return $bill;
                                        })->toArray();
                                    }

                                    $record->bills = $bills;
                                    $record->monthly_payment = $rentAmount;
                                    $record->lease_status = 'active';
                                    $record->payment_status = 'unpaid'; 
                                    $record->save();

                                    \Filament\Notifications\Notification::make()
                                        ->title('Bills Updated')
                                        ->success()
                                        ->send();

                                    $user = User::find($record->user_id);

                                    \Filament\Notifications\Notification::make()
                                        ->title('Bills Updated')
                                        ->body('The bills for your lease period have been updated.')
                                        ->success()
                                        ->sendToDatabase($user);
                                } else {
                                    \Filament\Notifications\Notification::make()
                                        ->title('No Update Needed')
                                        ->info()
                                        ->body('It\'s too early to update the bills for this tenant.')
                                        ->send();
                                }
                            } else {
                                \Filament\Notifications\Notification::make()
                                    ->title('Error')
                                    ->danger()
                                    ->body('Lease due date is not set for this tenant.')
                                    ->send();
                            }
                        }),
                    Tables\Actions\EditAction::make()->color('gray')->label('Add Monthly Bills'),
                    Tables\Actions\DeleteAction::make()->label('Archive'),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make()->label('Permanent Delete'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Actions')
            ])
            ->bulkActions([
                ExportBulkAction::make()->label('Generate Selected Records'),
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ])
            ->poll('3s');
    }
}