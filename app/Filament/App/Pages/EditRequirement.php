<?php

namespace App\Filament\App\Pages;

use App\Models\Application;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;

class EditRequirement extends Page implements Forms\Contracts\HasForms
{
    use Forms\Concerns\InteractsWithForms;

    protected static ?string $navigationLabel = 'Edit Application';
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static string $view = 'filament.app.pages.edit-requirement';
    protected static bool $shouldRegisterNavigation = false;

    public ?array $data = [];
    public ?Application $application;

    public function mount(): void
    {
        $concourseId = request()->query('concourse_id');
        $spaceId = request()->query('space_id');
        $userId = Auth::id();

        $this->application = Application::where('concourse_id', $concourseId)
            ->where('space_id', $spaceId)
            ->where('user_id', $userId)
            ->firstOrFail();

        $this->form->fill($this->application->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Business Information')
                    ->schema([
                        Forms\Components\TextInput::make('business_name')
                            ->label('Business Name'),
                        Forms\Components\TextInput::make('owner_name')
                            ->label('Owner Name'),
                        Forms\Components\TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->default(fn() => $this->application->email)
                            ->disabled(),
                        Forms\Components\TextInput::make('phone_number')
                            ->label('Phone Number')
                            ->default(fn() => $this->application->phone_number)
                            ->disabled(),
                        Forms\Components\TextInput::make('address')
                            ->label('Permanent Address')
                            ->default(fn() => $this->application->address)
                            ->columnSpanFull(),
                        Forms\Components\Select::make('business_type')
                            ->label('Business Type')
                            ->options([
                                'food' => 'Food',
                                'non-food' => 'Non Food',
                                'other' => 'Other',
                            ])
                            ->native(false),
                        Forms\Components\DatePicker::make('expiration_date')
                            ->label('Lease Agreement Date')
                            ->native(false),
                        Forms\Components\Repeater::make('requirements')
                            ->schema([
                                Forms\Components\Grid::make(2)->schema([
                                    Forms\Components\TextInput::make('name'),
                                    Forms\Components\TextInput::make('status')
                                        ->default('pending')
                                        ->extraInputAttributes(['class' => 'capitalize'])
                                        ->disabled(),
                                ]),
                                Forms\Components\FileUpload::make('attachment')
                                    ->image()
                                    ->label('Attachment')
                                    ->maxSize(5120)
                                    ->imageEditor()
                                    ->openable()
                                    ->downloadable()
                                    ->preserveFilenames()
                                    ->columnSpanFull(),
                            ])->columnSpanFull(),

                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        // No need to filter fillable data, as we're using $fillable in the model
        $this->application->update($data);

        Notification::make()
            ->success()
            ->title('Application updated successfully')
            ->send();
    }
}
