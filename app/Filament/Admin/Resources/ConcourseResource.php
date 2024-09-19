<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ConcourseResource\Pages;
use App\Filament\Admin\Resources\ConcourseResource\RelationManagers;
use App\Models\Concourse;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ConcourseResource extends Resource
{
    protected static ?string $model = Concourse::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                                Forms\Components\Select::make('rate')
                                    ->options([
                                        '100' => 'City',
                                        '200' => 'Province',
                                    ])
                                    ->required(),
                            ])->columns(2),
                        ]),

                        Forms\Components\Section::make('Attachments')->schema([
                            Forms\Components\FileUpload::make('image')
                                ->image()
                                ->imageEditor()
                                ->label('Concourse Image'),
                            Forms\Components\FileUpload::make('layout')
                                ->image()
                                ->imageEditor()
                                ->label('Space Layout'),
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
                    ->width(200)
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
                Tables\Columns\TextColumn::make('rate')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-m-bolt-slash')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
        ];
    }
}
