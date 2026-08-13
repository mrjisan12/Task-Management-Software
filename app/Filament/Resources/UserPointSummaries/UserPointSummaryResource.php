<?php

namespace App\Filament\Resources\UserPointSummaries;

use App\Filament\Resources\UserPointSummaries\Pages\CreateUserPointSummary;
use App\Filament\Resources\UserPointSummaries\Pages\EditUserPointSummary;
use App\Filament\Resources\UserPointSummaries\Pages\ListUserPointSummaries;
use App\Filament\Resources\UserPointSummaries\Pages\ViewUserPointSummary;
use App\Filament\Resources\UserPointSummaries\Schemas\UserPointSummaryForm;
use App\Filament\Resources\UserPointSummaries\Schemas\UserPointSummaryInfolist;
use App\Filament\Resources\UserPointSummaries\Tables\UserPointSummariesTable;
use App\Models\UserPointSummary;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserPointSummaryResource extends Resource
{
    protected static ?string $model = UserPointSummary::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return UserPointSummaryForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return UserPointSummaryInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserPointSummariesTable::configure($table);
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
            'index' => ListUserPointSummaries::route('/'),
            'create' => CreateUserPointSummary::route('/create'),
            'view' => ViewUserPointSummary::route('/{record}'),
            'edit' => EditUserPointSummary::route('/{record}/edit'),
        ];
    }
}
