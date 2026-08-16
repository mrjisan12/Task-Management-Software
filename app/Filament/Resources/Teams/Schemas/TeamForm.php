<?php

namespace App\Filament\Resources\Teams\Schemas;

use App\Models\Department;
use App\Models\User;
use App\Support\AdminCompanyScope;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class TeamForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                AdminCompanyScope::companySelect(Select::make('company_id'))
                    ->live()
                    ->afterStateUpdated(fn (Set $set) => $set('department_id', null)),
                Select::make('department_id')
                    ->options(fn (Get $get): array => Department::query()
                        ->where('company_id', $get('company_id'))
                        ->where('is_active', true)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->disabled(fn (Get $get): bool => blank($get('company_id'))),
                Select::make('lead_user_id')
                    ->options(fn (Get $get): array => User::query()
                        ->whereHas('companyMemberships', fn ($query) => $query
                            ->where('company_id', $get('company_id') ?: AdminCompanyScope::companyId())
                            ->where('status', 'active'))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload(),
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Textarea::make('description')
                    ->rows(3)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
            ]);
    }
}
