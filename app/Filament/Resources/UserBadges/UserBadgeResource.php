<?php

namespace App\Filament\Resources\UserBadges;

use App\Filament\Resources\UserBadges\Pages\CreateUserBadge;
use App\Filament\Resources\UserBadges\Pages\EditUserBadge;
use App\Filament\Resources\UserBadges\Pages\ListUserBadges;
use App\Filament\Resources\UserBadges\Pages\ViewUserBadge;
use App\Filament\Resources\UserBadges\Schemas\UserBadgeForm;
use App\Filament\Resources\UserBadges\Schemas\UserBadgeInfolist;
use App\Filament\Resources\UserBadges\Tables\UserBadgesTable;
use App\Models\UserBadge;
use App\Support\AdminCompanyScope;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserBadgeResource extends Resource
{
    protected static ?string $model = UserBadge::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UserBadgeForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserBadgeInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserBadgesTable::configure($table);
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
            'index' => ListUserBadges::route('/'),
            'create' => CreateUserBadge::route('/create'),
            'view' => ViewUserBadge::route('/{record}'),
            'edit' => EditUserBadge::route('/{record}/edit'),
        ];
    }
}
