<?php

namespace App\Filament\Resources\PointRules;

use App\Filament\Resources\PointRules\Pages\CreatePointRule;
use App\Filament\Resources\PointRules\Pages\EditPointRule;
use App\Filament\Resources\PointRules\Pages\ListPointRules;
use App\Filament\Resources\PointRules\Schemas\PointRuleForm;
use App\Filament\Resources\PointRules\Tables\PointRulesTable;
use App\Models\PointRule;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PointRuleResource extends Resource
{
    protected static ?string $model = PointRule::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PointRuleForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PointRulesTable::configure($table);
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
            'index' => ListPointRules::route('/'),
            'create' => CreatePointRule::route('/create'),
            'edit' => EditPointRule::route('/{record}/edit'),
        ];
    }
}
