<?php

namespace App\Filament\Resources\CompanyJoinRequests\Pages;

use App\Filament\Resources\CompanyJoinRequests\CompanyJoinRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCompanyJoinRequests extends ListRecords
{
    protected static string $resource = CompanyJoinRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
