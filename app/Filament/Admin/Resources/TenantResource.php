<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\TenantResource\Pages;
use App\Filament\Admin\Resources\TenantResource\RelationManagers;
use App\Filament\Admin\Resources\TenantResource\Widgets\TenantsRevenue;
use App\Models\Tenant;
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
                            Forms\Components\Select::make('owner_id')
                                ->relationship('owner', 'name')
                                ->label('Owner')
                                ->preload()
                                ->required()
                                ->disabled(),
                        ])->columns(2),
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
                            ])->columnSpanFull()
                    ])->columns(2),
                ])->columnSpan([
                    'sm' => 3,
                    'md' => 3,
                    'lg' => 2
                ]),
                Forms\Components\Grid::make(1)->schema([
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
                    ->numeric()
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('space.name')
                    ->numeric()
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('space.price')
                    ->label('Rate')
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
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\TextColumn::make('lease_status')
                    ->label('Lease Status')
                    ->badge()
                    ->extraAttributes(['class' => 'capitalize'])
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: false),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-m-bolt-slash')
                    ->sortable(),
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
            ]);
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
