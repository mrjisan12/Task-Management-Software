<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Support\AdminCompanyScope;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->revealable()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->dehydrateStateUsing(fn (string $state): string => Hash::make($state)),
                TextInput::make('timezone')
                    ->default('UTC')
                    ->maxLength(255),
                TextInput::make('locale')
                    ->default('en')
                    ->maxLength(12),
                Toggle::make('is_active')
                    ->default(true)
                    ->required(),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->options(fn (): array => Role::query()
                        ->when(! AdminCompanyScope::isPlatformAdmin(), fn ($query) => $query
                            ->whereNotIn('name', ['super_admin', 'platform_admin']))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->map(fn (string $role): string => str($role)->replace('_', ' ')->headline()->toString())
                        ->all())
                    ->multiple()
                    ->preload()
                    ->searchable(),
            ]);
    }
}
