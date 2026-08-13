<?php

namespace App\Filament\Resources\Tasks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class TasksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('company.name')->searchable()->sortable(),
                TextColumn::make('creator.name')->label('Creator')->searchable(),
                TextColumn::make('status.name')->badge()->sortable(),
                TextColumn::make('priority.name')->badge()->sortable(),
                TextColumn::make('team.name')->placeholder('No team')->toggleable(),
                TextColumn::make('due_at')->dateTime()->sortable(),
                TextColumn::make('completed_at')->dateTime()->placeholder('Not completed')->sortable(),
            ])
            ->filters([
                SelectFilter::make('company_id')->relationship('company', 'name')->label('Company'),
                SelectFilter::make('task_status_id')->relationship('status', 'name')->label('Status'),
                SelectFilter::make('task_priority_id')->relationship('priority', 'name')->label('Priority'),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
