<?php

namespace App\Filament\Resources\Achievements\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AchievementForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')->relationship('company', 'name')->searchable()->preload(),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('slug')->required()->maxLength(255),
                TextInput::make('icon')->maxLength(255),
                Select::make('rule_key')->options([
                    'deadline_sprinter' => 'Deadline Sprinter',
                    'very_late_completion' => 'Very Late Completion',
                    'daily_task_burst' => 'Daily Task Burst',
                ])->required(),
                TextInput::make('points_reward')->numeric()->default(0),
                Toggle::make('is_repeatable')->default(false),
                Toggle::make('is_active')->default(true),
                KeyValue::make('requirements')->columnSpanFull(),
                Textarea::make('description')->rows(3)->columnSpanFull(),
            ]);
    }
}
