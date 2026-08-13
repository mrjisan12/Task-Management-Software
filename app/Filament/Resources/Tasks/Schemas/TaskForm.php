<?php

namespace App\Filament\Resources\Tasks\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')->relationship('company', 'name')->searchable()->preload()->required(),
                Select::make('created_by')->relationship('creator', 'name')->searchable()->preload()->required(),
                Select::make('team_id')->relationship('team', 'name')->searchable()->preload(),
                Select::make('task_status_id')->relationship('status', 'name')->searchable()->preload()->required(),
                Select::make('task_priority_id')->relationship('priority', 'name')->searchable()->preload()->required(),
                Select::make('task_category_id')->relationship('category', 'name')->searchable()->preload(),
                TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Textarea::make('description')->rows(4)->columnSpanFull(),
                DateTimePicker::make('due_at'),
                TextInput::make('estimated_minutes')->numeric()->minValue(1),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('completed_at'),
                Select::make('completed_by')->relationship('completedBy', 'name')->searchable()->preload(),
                Textarea::make('completion_comment')->rows(3)->columnSpanFull(),
            ]);
    }
}
