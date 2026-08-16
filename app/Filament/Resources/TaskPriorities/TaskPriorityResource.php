<?php

namespace App\Filament\Resources\TaskPriorities;

use App\Filament\Resources\TaskPriorities\Pages\CreateTaskPriority;
use App\Filament\Resources\TaskPriorities\Pages\EditTaskPriority;
use App\Filament\Resources\TaskPriorities\Pages\ListTaskPriorities;
use App\Filament\Resources\TaskPriorities\Schemas\TaskPriorityForm;
use App\Filament\Resources\TaskPriorities\Tables\TaskPrioritiesTable;
use App\Models\TaskPriority;
use App\Support\AdminCompanyScope;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TaskPriorityResource extends Resource
{
    protected static ?string $model = TaskPriority::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return TaskPriorityForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TaskPrioritiesTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return AdminCompanyScope::companyOrGlobalQuery(parent::getEloquentQuery());
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return AdminCompanyScope::companyOrGlobalQuery(parent::getRecordRouteBindingEloquentQuery());
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTaskPriorities::route('/'),
            'create' => CreateTaskPriority::route('/create'),
            'edit' => EditTaskPriority::route('/{record}/edit'),
        ];
    }
}
