<?php

namespace App\Filament\Resources\Leaderboards;

use App\Filament\Resources\Leaderboards\Pages\CreateLeaderboard;
use App\Filament\Resources\Leaderboards\Pages\EditLeaderboard;
use App\Filament\Resources\Leaderboards\Pages\ListLeaderboards;
use App\Filament\Resources\Leaderboards\Pages\ViewLeaderboard;
use App\Filament\Resources\Leaderboards\Schemas\LeaderboardForm;
use App\Filament\Resources\Leaderboards\Schemas\LeaderboardInfolist;
use App\Filament\Resources\Leaderboards\Tables\LeaderboardsTable;
use App\Models\Leaderboard;
use App\Support\AdminCompanyScope;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class LeaderboardResource extends Resource
{
    protected static ?string $model = Leaderboard::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return LeaderboardForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return LeaderboardInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeaderboardsTable::configure($table);
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
            'index' => ListLeaderboards::route('/'),
            'create' => CreateLeaderboard::route('/create'),
            'view' => ViewLeaderboard::route('/{record}'),
            'edit' => EditLeaderboard::route('/{record}/edit'),
        ];
    }
}
