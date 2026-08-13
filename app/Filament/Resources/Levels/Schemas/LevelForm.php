<?php

namespace App\Filament\Resources\Levels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LevelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')->relationship('company', 'name')->searchable()->preload(),
                TextInput::make('name')->required()->maxLength(255),
                TextInput::make('required_xp')->numeric()->required()->minValue(0),
                TextInput::make('icon')->maxLength(255),
                TextInput::make('sort_order')->numeric()->default(0),
                Toggle::make('is_active')->default(true),
                Textarea::make('description')->rows(3)->columnSpanFull(),
            ]);
    }
}
