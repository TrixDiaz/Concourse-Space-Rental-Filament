<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\ApplicationResource\Pages;
use App\Filament\Admin\Resources\ApplicationResource\RelationManagers;
use App\Models\Application;
use App\Models\AppRequirement;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApplicationResource extends Resource
{
    protected static ?string $navigationGroup = 'Tenants Settings';

    protected static ?string $navigationLabel = 'Applications';

    protected static ?string $model = Application::class;

    protected static ?string $navigationIcon = 'heroicon-o-folder';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Forms\Components\Grid::make()->schema([
                            Forms\Components\Select::make('concourse_id')
                                ->relationship('concourse', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->disabledOn('edit'),
                            Forms\Components\Select::make('space_id')
                                ->relationship('space', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->disabledOn('edit'),
                            Forms\Components\Select::make('user_id')
                                ->relationship('user', 'name')
                                ->required()
                                ->searchable()
                                ->preload()
                                ->label('Tenant')
                                ->disabledOn('edit'),
                            Forms\Components\TextInput::make('business_name')
                                ->maxLength(255)
                                ->default(null),
                            Forms\Components\TextInput::make('owner_name')
                                ->maxLength(255)
                                ->default(null),
                            Forms\Components\TextInput::make('email')
                                ->email()
                                ->maxLength(255)
                                ->default(null),
                        ])->columns(3),
                        Forms\Components\TextInput::make('address')
                            ->maxLength(255)
                            ->default(null)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('phone_number')
                            ->tel()
                            ->maxLength(255)
                            ->default(null),
                        Forms\Components\Select::make('business_type')
                            ->label('Business Type')
                            ->extraInputAttributes(['class' => 'capitalize'])
                            ->options([
                                'food' => 'Food',
                                'non-food' => 'Non Food',
                                'other' => 'Other',
                            ])
                            ->native(false),
                        Forms\Components\TextInput::make('concourse_lease_term')
                            ->label('Concourse Lease Term')
                            ->disabled()
                            ->suffix('Months'),
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->native(false)
                            ->extraInputAttributes(['class' => 'capitalize']),
                        Forms\Components\TextInput::make('remarks')
                            ->maxLength(255)
                            ->default(null)
                            ->columnSpanFull(),
                    ])->columns(2),
                Forms\Components\Section::make('List of Required Documents')
                    ->description('Approved each documents for the application')
                    ->schema([
                        Forms\Components\Repeater::make('appRequirements')
                            ->relationship()
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\FileUpload::make('file')
                                        ->disk('public')
                                        ->directory('app-requirements')
                                        ->visibility('public')
                                        ->downloadable()
                                        ->disabled()
                                        ->openable()
                                        ->columnSpanFull(),
                                ])->columnSpan([
                                    'sm' => 3,
                                    'md' => 3,
                                    'lg' => 2
                                ]),
                                Forms\Components\Grid::make(1)->schema([
                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->disabled()
                                        ->readOnly(),
                                    Forms\Components\Select::make('status')
                                        ->required()
                                        ->options([
                                            'pending' => 'Pending',
                                            'approved' => 'Approved',
                                            'rejected' => 'Rejected',
                                        ]),
                                ])->columnSpan([
                                    'sm' => 3,
                                    'md' => 3,
                                    'lg' => 1
                                ]),
                            ])
                            ->columns(3)
                            ->columnSpanFull()
                            ->defaultItems(0)
                            ->disableItemCreation()
                            ->disableItemDeletion(),
                    ])->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->sortable()
                    ->label('Tenant'),
                Tables\Columns\TextColumn::make('concourse.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('space.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('business_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('owner_name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('address')
                    ->searchable()
                    ->limit(20)
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('phone_number')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('business_type')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('concourse_lease_term')
                    ->label('Due Date')
                    ->date()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        if ($record->concourse_lease_term) {
                            return $record->created_at->addMonths($record->concourse_lease_term);
                        }
                        return null;
                    }),
                Tables\Columns\TextColumn::make('status')
                    ->searchable()
                    ->badge()
                    ->extraAttributes(['class' => 'capitalize']),
                Tables\Columns\TextColumn::make('remarks')
                    ->searchable(),
                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Active')
                    ->onIcon('heroicon-m-bolt')
                    ->offIcon('heroicon-m-bolt-slash')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
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
            ])
            ->poll('3s');
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
            'index' => Pages\ListApplications::route('/'),
            'create' => Pages\CreateApplication::route('/create'),
            'edit' => Pages\EditApplication::route('/{record}/edit'),
        ];
    }
}
