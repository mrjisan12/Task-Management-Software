<?php

namespace App\Filament\Resources\CompanyJoinRequests\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CompanyJoinRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('company_id')
                    ->relationship('company', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->searchable()
                    ->preload()
                    ->required(),
                TextInput::make('code_used')
                    ->required()
                    ->maxLength(32),
                Select::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required()
                    ->default('pending'),
                Select::make('reviewed_by')
                    ->relationship('reviewer', 'name')
                    ->searchable()
                    ->preload(),
                DateTimePicker::make('reviewed_at'),
                Textarea::make('review_note')
                    ->default(null)
                    ->columnSpanFull(),
            ]);
    }
}
