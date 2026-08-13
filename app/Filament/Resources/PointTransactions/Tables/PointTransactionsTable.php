<?php

namespace App\Filament\Resources\PointTransactions\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PointTransactionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->searchable()->sortable(),
                TextColumn::make('company.name')->searchable()->sortable(),
                TextColumn::make('task.title')->limit(32)->placeholder('No task'),
                TextColumn::make('type')->badge(),
                TextColumn::make('points')->sortable(),
                TextColumn::make('description')->searchable()->limit(40),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('company_id')->relationship('company', 'name')->label('Company'),
                SelectFilter::make('type')->options([
                    'award' => 'Award',
                    'bonus' => 'Bonus',
                    'deduction' => 'Deduction',
                    'reversal' => 'Reversal',
                    'adjustment' => 'Adjustment',
                    'metric' => 'Metric',
                ]),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
