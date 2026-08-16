<?php

namespace App\Filament\Resources\Badges\Schemas;

use App\Support\AdminCompanyScope;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class BadgeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                AdminCompanyScope::companySelect(Select::make('company_id'), false),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('slug')->required()->maxLength(255),
                TextInput::make('icon')->maxLength(255),
                Select::make('rule_key')->options([
                    'tasks_completed_count' => 'Tasks Completed Count',
                    'on_time_tasks_count' => 'On-Time Tasks Count',
                    'streak_days' => 'Streak Days',
                    'monthly_points' => 'Monthly Points',
                ])->required(),
                TextInput::make('points_reward')->numeric()->default(0),
                Toggle::make('is_active')->default(true),
                KeyValue::make('requirements')->columnSpanFull(),
                Textarea::make('description')->rows(3)->columnSpanFull(),
            ]);
    }
}
