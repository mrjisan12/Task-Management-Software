<?php

namespace App\Filament\Resources\UserAchievements;

use App\Filament\Resources\UserAchievements\Pages\CreateUserAchievement;
use App\Filament\Resources\UserAchievements\Pages\EditUserAchievement;
use App\Filament\Resources\UserAchievements\Pages\ListUserAchievements;
use App\Filament\Resources\UserAchievements\Pages\ViewUserAchievement;
use App\Filament\Resources\UserAchievements\Schemas\UserAchievementForm;
use App\Filament\Resources\UserAchievements\Schemas\UserAchievementInfolist;
use App\Filament\Resources\UserAchievements\Tables\UserAchievementsTable;
use App\Models\UserAchievement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserAchievementResource extends Resource
{
    protected static ?string $model = UserAchievement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UserAchievementForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserAchievementInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserAchievementsTable::configure($table);
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
            'index' => ListUserAchievements::route('/'),
            'create' => CreateUserAchievement::route('/create'),
            'view' => ViewUserAchievement::route('/{record}'),
            'edit' => EditUserAchievement::route('/{record}/edit'),
        ];
    }
}
