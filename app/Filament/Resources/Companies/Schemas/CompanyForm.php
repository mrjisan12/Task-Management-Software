<?php

namespace App\Filament\Resources\Companies\Schemas;

use App\Models\Company;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CompanyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, callable $set) => $operation === 'create'
                        ? $set('slug', Str::slug($state))
                        : null),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('code')
                    ->maxLength(32)
                    ->unique(ignoreRecord: true)
                    ->helperText('Leave blank to auto-generate a secure company join code.')
                    ->default(fn () => Company::generateCode('Company')),
                Select::make('join_mode')
                    ->options([
                        'open' => 'Open Join',
                        'approval_required' => 'Approval Required',
                        'closed' => 'Closed',
                    ])
                    ->default('approval_required')
                    ->required(),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->default('active')
                    ->required(),
                Toggle::make('settings.leaderboard_visible')
                    ->label('Leaderboard visible')
                    ->default(true),
            ]);
    }
}
