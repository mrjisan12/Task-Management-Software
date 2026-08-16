<?php

namespace App\Filament\Resources\PointTransactions;

use App\Filament\Resources\PointTransactions\Pages\CreatePointTransaction;
use App\Filament\Resources\PointTransactions\Pages\EditPointTransaction;
use App\Filament\Resources\PointTransactions\Pages\ListPointTransactions;
use App\Filament\Resources\PointTransactions\Pages\ViewPointTransaction;
use App\Filament\Resources\PointTransactions\Schemas\PointTransactionForm;
use App\Filament\Resources\PointTransactions\Schemas\PointTransactionInfolist;
use App\Filament\Resources\PointTransactions\Tables\PointTransactionsTable;
use App\Models\PointTransaction;
use App\Support\AdminCompanyScope;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PointTransactionResource extends Resource
{
    protected static ?string $model = PointTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return PointTransactionForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return PointTransactionInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PointTransactionsTable::configure($table);
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
            'index' => ListPointTransactions::route('/'),
            'create' => CreatePointTransaction::route('/create'),
            'view' => ViewPointTransaction::route('/{record}'),
            'edit' => EditPointTransaction::route('/{record}/edit'),
        ];
    }
}
