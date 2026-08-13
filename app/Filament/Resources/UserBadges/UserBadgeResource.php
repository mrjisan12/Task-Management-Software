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
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

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
