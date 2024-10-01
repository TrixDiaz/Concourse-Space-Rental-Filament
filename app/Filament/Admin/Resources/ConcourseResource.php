<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ConcourseResource\Pages;
use App\Filament\Admin\Resources\ConcourseResource\RelationManagers;
use App\Models\Concourse;
use App\Models\ConcourseRate;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use pxlrbt\FilamentExcel\Actions\Tables\ExportBulkAction;

class ConcourseResource extends Resource
{
    protected static ?string $navigationGroup = 'Concourse Settings';

    protected static ?string $navigationLabel = 'Concourse';

    protected static ?string $model = Concourse::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)->schema([
                    Forms\Components\Section::make()->schema([
                        Forms\Components\Section::make('Concourse Details')->schema([
                            Forms\Components\TextInput::make('address')
                                ->maxLength(255)
                                ->required(),
                            Forms\Components\Grid::make()->schema([
                                Forms\Components\TextInput::make('name')
                                    ->required()
                                    ->maxLength(255),
                                Forms\Components\Select::make('rate_id')
                                    ->native(false)
                                    ->preload()
                                    ->relationship('concourseRate', 'name')
                                    ->required(),
                                Forms\Components\Select::make('lease_term')
                                    ->default(12)
                                    ->options([
                                        '1' => '1 month',
                                        '2' => '2 months',
                                        '3' => '3 months',
                                        '6' => '6 months',
                                        '12' => '1 year',
                                        '24' => '2 years',
                                        '36' => '3 years',
                                    ])
                                    ->native(false)
                                    ->required(),
                            ])->columns(3),
                        ]),

                        Forms\Components\Section::make('Attachments')->schema([
                            Forms\Components\FileUpload::make('image')
                                ->image()
                                ->label('Concourse Image')
                                ->imageEditor()
                                ->openable()
                                ->downloadable(),
                            Forms\Components\FileUpload::make('layout')
                                ->image()
                                ->label('Space Layout')
                                ->imageEditor()
                                ->openable()
                                ->downloadable(),
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
                                    $category = Concourse::find($record->id);
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
                                    $category = Concourse::find($record->id);
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
                    ]),

                ])->columns(3)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->square()
                    ->width(150)
                    ->height(150)
                    ->label('Concourse Image')
                    ->defaultImageUrl(fn($record) => $record->image === null ? asset('https://placehold.co/600x800') : null),
                Tables\Columns\ImageColumn::make('layout')
                    ->square()
                    ->width(150)
                    ->height(150)
                    ->label('Space Layout')
                    ->defaultImageUrl(fn($record) => $record->layout === null ? asset('https://placehold.co/600x800') : null)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('name')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('concourseRate.price')
                    ->label('Rate')
                    ->money('PHP')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('spaces')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-m-bolt-slash')
                    ->sortable(),
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
                    Tables\Actions\Action::make('viewSpaces')
                        ->label('View Layout')
                        ->icon('heroicon-o-map')
                        ->url(fn(Concourse $record): string => static::getUrl('view-spaces', ['record' => $record]))
                        ->color('success'),
                ])
                    ->icon('heroicon-m-ellipsis-vertical')
                    ->tooltip('Actions')
            ])
            ->bulkActions([
                ExportBulkAction::make(),
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->poll('30s');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConcourses::route('/'),
            'create' => Pages\CreateConcourse::route('/create'),
            'edit' => Pages\EditConcourse::route('/{record}/edit'),
            'view-spaces' => Pages\ViewSpaceConcourses::route('/{record}/spaces'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
