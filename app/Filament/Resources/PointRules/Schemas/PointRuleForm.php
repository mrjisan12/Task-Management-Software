<?php

namespace App\Filament\Resources\PointRules\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class PointRuleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')->relationship('company', 'name')->searchable()->preload(),
                TextInput::make('key')->required()->maxLength(255),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('points')->numeric()->required()->default(0),
                Toggle::make('is_active')->default(true),
            ]);
    }
}
