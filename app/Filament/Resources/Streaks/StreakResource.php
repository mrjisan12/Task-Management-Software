<?php

namespace App\Filament\Resources\Streaks;

use App\Filament\Resources\Streaks\Pages\CreateStreak;
use App\Filament\Resources\Streaks\Pages\EditStreak;
use App\Filament\Resources\Streaks\Pages\ListStreaks;
use App\Filament\Resources\Streaks\Pages\ViewStreak;
use App\Filament\Resources\Streaks\Schemas\StreakForm;
use App\Filament\Resources\Streaks\Schemas\StreakInfolist;
use App\Filament\Resources\Streaks\Tables\StreaksTable;
use App\Models\Streak;
use App\Support\AdminCompanyScope;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StreakResource extends Resource
{
    protected static ?string $model = Streak::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return StreakForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return StreakInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StreaksTable::configure($table);
    }

    public static function shouldRegisterNavigation(): bool
    {
        return AdminCompanyScope::isPlatformAdmin();
    }

    public static function getEloquentQuery(): Builder
    {
        return AdminCompanyScope::companyQuery(parent::getEloquentQuery());
    }

    public static function canCreate(): bool
    {
        return AdminCompanyScope::isPlatformAdmin();
    }

    public static function canEdit($record): bool
    {
        return AdminCompanyScope::isPlatformAdmin();
    }

    public static function canDelete($record): bool
    {
        return AdminCompanyScope::isPlatformAdmin();
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return AdminCompanyScope::companyQuery(parent::getRecordRouteBindingEloquentQuery());
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
            'index' => ListStreaks::route('/'),
            'create' => CreateStreak::route('/create'),
            'view' => ViewStreak::route('/{record}'),
            'edit' => EditStreak::route('/{record}/edit'),
        ];
    }
}
