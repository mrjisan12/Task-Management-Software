<?php

namespace App\Filament\Resources\TaskPriorities\Schemas;

use App\Support\AdminCompanyScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class TaskPriorityForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                AdminCompanyScope::companySelect(Select::make('company_id'), false),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('slug')->required()->maxLength(255),
                TextInput::make('weight')->numeric()->default(0),
                TextInput::make('color')->default('gray')->maxLength(255),
                TextInput::make('sort_order')->numeric()->default(0),
                Toggle::make('is_active')->default(true),
            ]);
    }
}
