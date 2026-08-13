<?php

namespace App\Filament\Resources\UserPointSummaries\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class UserPointSummariesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->searchable()->sortable(),
                TextColumn::make('company.name')->searchable()->sortable(),
                TextColumn::make('total_points')->sortable(),
                TextColumn::make('monthly_points')->sortable(),
                TextColumn::make('xp')->sortable(),
                TextColumn::make('tasks_completed')->sortable(),
                TextColumn::make('last_recalculated_at')->dateTime()->placeholder('Never'),
            ])
            ->filters([
                SelectFilter::make('company_id')->relationship('company', 'name')->label('Company'),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
