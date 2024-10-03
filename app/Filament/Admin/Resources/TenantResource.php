<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TenantResource\Pages;
use App\Filament\Admin\Resources\TenantResource\RelationManagers;
use App\Filament\Admin\Resources\TenantResource\Widgets\TenantsRevenue;
use App\Models\Tenant;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Carbon\Carbon;
use Filament\Notifications\Notification;

class TenantResource extends Resource
{
    protected static ?string $navigationGroup = 'Tenants Settings';

    protected static ?string $navigationLabel = 'Tenants';

    protected static ?string $model = Tenant::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-group';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Section::make()
                        ->schema([
                            Forms\Components\Select::make('tenant_id')
                                ->relationship('tenant', 'name')
                                ->label('Tenant Name')
                                ->preload()
                                ->required()
                                ->disabled(),
                            Forms\Components\Select::make('concourse_id')
                                ->relationship('concourse', 'name')
                                ->label('Concourse')
                                ->preload()
                                ->required()
                                ->disabled(),
                            Forms\Components\Select::make('space_id')
                                ->relationship('space', 'name')
                                ->label('Space')
                                ->preload()
                                ->required()
                                ->disabled(),
                            Forms\Components\DatePicker::make('lease_start')
                                ->label('Lease Start')
                                ->native(false)
                                ->disabled(),
                            Forms\Components\DatePicker::make('lease_due')
                                ->label('Lease Due')
                                ->native(false)
                                ->required(),
                            Forms\Components\TextInput::make('lease_term')
                                ->label('Lease Term')
                                ->disabled(),
                        ])->columns(3),
                    Forms\Components\Section::make('Bills Utility')->description('Add the utility bills for the tenant')->schema([
                        Repeater::make('bills')
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
                            ->columnSpanFull()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $total = collect($state)->sum('amount');
                                $set('monthly_payment', $total);
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
                        Forms\Components\Select::make('payment_status')
                            ->label('Payment Status')
                            ->native(false)
                            ->options([
                                'paid' => 'Paid',
                                'unpaid' => 'Unpaid',
                                'overdue' => 'Overdue',
                                'pending' => 'Pending',
                            ]),
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
                                $category = Tenant::find($record->id);
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
                                $category = Tenant::find($record->id);
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
            ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('concourse.name')
                    ->label('Concourse')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('concourse.address')
                    ->label('Address')
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('space.name')
                    ->label('Name')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('space.price')
                    ->label('Price')
                    ->prefix('₱')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('owner.name')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('lease_start')
                    ->dateTime('F j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('lease_end')
                    ->dateTime('F j, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('lease_term')
                    ->label('Lease Term')
                    ->formatStateUsing(fn($state) => $state . ' Months')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('lease_status')
                    ->label('Lease Status')
                    ->badge()
                    ->extraAttributes(['class' => 'capitalize'])
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('monthly_payment')
                    ->label('Monthly Payment')
                    ->prefix('₱')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('payment_status')
                    ->label('Payment Status')
                    ->extraAttributes(['class' => 'capitalize'])
                    ->badge()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('F j, Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('is_active')
                    ->options([
                        '1' => 'Active',
                        '0' => 'Inactive',
                    ])
                    ->label('Active'),
            ])
            ->actions([
                Tables\Actions\Action::make('updateBills')
                    ->label('Update Bills')
                    ->icon('heroicon-m-currency-dollar')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->action(function (Tenant $record) {
                        if ($record->lease_due) {
                            $leaseDate = \Carbon\Carbon::parse($record->lease_due);
                            $today = \Carbon\Carbon::today();
                            $sevenDaysBefore = $leaseDate->copy()->subDays(7);

                            if ($today->gte($sevenDaysBefore) || $today->isSameDay($leaseDate)) {
                                $rentAmount = $record->space->price ?? 0;
                                $bills = $record->bills ?? [];

                                $billExists = collect($bills)->contains(function ($bill) use ($leaseDate) {
                                    return isset($bill['name']) && $bill['name'] == 'Monthly Rent' &&
                                        isset($bill['for_month']) && $bill['for_month'] == $leaseDate->format('Y-m');
                                });

                                if (!$billExists) {
                                    $bills[] = [
                                        'name' => 'Monthly Rent',
                                        'amount' => $rentAmount,
                                        'for_month' => $leaseDate->format('Y-m'),
                                        'due_date' => $leaseDate->toDateString(),
                                    ];

                                    $record->bills = $bills;
                                    $record->monthly_payment =  $rentAmount;;
                                    $record->lease_status = 'due_soon';
                                    $record->payment_status = 'pending';
                                    $record->save();

                                    \Filament\Notifications\Notification::make()
                                        ->title('Bills Updated')
                                        ->success()
                                        ->send();

                                    $user = User::find($record->tenant_id);

                                    \Filament\Notifications\Notification::make()
                                        ->title('Bills Updated')
                                        ->body('The bills for your lease period has been updated.')
                                        ->success()
                                        ->sendToDatabase($user);
                                } else {
                                    \Filament\Notifications\Notification::make()
                                        ->title('No Update Needed')
                                        ->info()
                                        ->body('The bill for this lease period already exists.')
                                        ->send();
                                }
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
                Tables\Actions\Action::make('notifyTenant')
                    ->label('Notify Tenant')
                    ->icon('heroicon-m-envelope')
                    ->color('info')
                    ->action(function (Tenant $record) {
                        $currentBill = collect($record->bills)->first(function ($bill) {
                            return $bill['name'] === 'Monthly Rent' &&
                                Carbon::parse($bill['due_date'])->isPast();
                        });

                        if ($currentBill) {
                            $user = User::find($record->tenant_id);

                            Notification::make()
                                ->title('Tenant Notified')
                                ->success()
                                ->body("A notification about the current rent bill has been sent to your {$record->space->name}.")
                                ->sendToDatabase($user);

                            Notification::make()
                                ->title('Tenant Notified')
                                ->success()
                                ->body("A notification about the current rent bill has been sent to {$record->tenant->name}.")
                                ->send();
                        } else {
                            Notification::make()
                                ->title('No Current Bill')
                                ->warning()
                                ->body('There is no current monthly rent bill for this tenant.')
                                ->send();
                        }
                    }),
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make()->color('info'),
                    Tables\Actions\EditAction::make()->color('primary'),
                    Tables\Actions\DeleteAction::make()->label('Archive'),
                    Tables\Actions\RestoreAction::make(),
                    Tables\Actions\ForceDeleteAction::make()->label('Permanent Delete'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Actions')
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->poll('30s');
    }

    public static function getWidgets(): array
    {
        return [
            TenantsRevenue::class,
        ];
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\PaymentRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }
}
