<?php

namespace App\Filament\App\Pages;

use Filament\Pages\Page;

class RequirementPage extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.app.pages.requirement-page';

    protected static bool $shouldRegisterNavigation = false;
}
