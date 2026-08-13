<?php

namespace App\Filament\Resources\CompanyJoinRequests;

use App\Filament\Resources\CompanyJoinRequests\Pages\CreateCompanyJoinRequest;
use App\Filament\Resources\CompanyJoinRequests\Pages\EditCompanyJoinRequest;
use App\Filament\Resources\CompanyJoinRequests\Pages\ListCompanyJoinRequests;
use App\Filament\Resources\CompanyJoinRequests\Pages\ViewCompanyJoinRequest;
use App\Filament\Resources\CompanyJoinRequests\Schemas\CompanyJoinRequestForm;
use App\Filament\Resources\CompanyJoinRequests\Schemas\CompanyJoinRequestInfolist;
use App\Filament\Resources\CompanyJoinRequests\Tables\CompanyJoinRequestsTable;
use App\Models\CompanyJoinRequest;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CompanyJoinRequestResource extends Resource
{
    protected static ?string $model = CompanyJoinRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedInboxArrowDown;

    protected static ?string $navigationLabel = 'Join Requests';

    protected static ?string $recordTitleAttribute = 'code_used';

    public static function form(Schema $schema): Schema
    {
        return CompanyJoinRequestForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CompanyJoinRequestInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompanyJoinRequestsTable::configure($table);
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
            'index' => ListCompanyJoinRequests::route('/'),
            'create' => CreateCompanyJoinRequest::route('/create'),
            'view' => ViewCompanyJoinRequest::route('/{record}'),
            'edit' => EditCompanyJoinRequest::route('/{record}/edit'),
        ];
    }
}
