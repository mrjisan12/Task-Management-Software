<?php

namespace App\Filament\Resources\Tasks\Schemas;

use App\Models\TaskCategory;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\Team;
use App\Models\User;
use App\Support\AdminCompanyScope;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                AdminCompanyScope::companySelect(Select::make('company_id')),
                Select::make('created_by')
                    ->options(fn (Get $get): array => self::companyUsers($get('company_id')))
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('team_id')
                    ->options(fn (Get $get): array => Team::query()
                        ->where('company_id', $get('company_id') ?: AdminCompanyScope::companyId())
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload(),
                Select::make('task_status_id')
                    ->options(fn (Get $get): array => TaskStatus::query()
                        ->where(fn ($query) => $query
                            ->where('company_id', $get('company_id') ?: AdminCompanyScope::companyId())
                            ->orWhereNull('company_id'))
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('task_priority_id')
                    ->options(fn (Get $get): array => TaskPriority::query()
                        ->where(fn ($query) => $query
                            ->where('company_id', $get('company_id') ?: AdminCompanyScope::companyId())
                            ->orWhereNull('company_id'))
                        ->where('is_active', true)
                        ->orderBy('sort_order')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('task_category_id')
                    ->options(fn (Get $get): array => TaskCategory::query()
                        ->where('company_id', $get('company_id') ?: AdminCompanyScope::companyId())
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload(),
                TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
                Textarea::make('description')->rows(4)->columnSpanFull(),
                DateTimePicker::make('due_at'),
                TextInput::make('estimated_minutes')->numeric()->minValue(1),
                DateTimePicker::make('started_at'),
                DateTimePicker::make('completed_at'),
                Select::make('completed_by')->options(fn (Get $get): array => self::companyUsers($get('company_id')))->searchable()->preload(),
                Textarea::make('completion_comment')->rows(3)->columnSpanFull(),
            ]);
    }

    private static function companyUsers(?int $companyId): array
    {
        return User::query()
            ->whereHas('companyMemberships', fn ($query) => $query
                ->where('company_id', $companyId ?: AdminCompanyScope::companyId())
                ->where('status', 'active'))
            ->orderBy('name')
            ->pluck('name', 'id')
            ->all();
    }
}
