<?php

namespace App\Filament\Resources\CompanyJoinRequests\Pages;

use App\Filament\Resources\CompanyJoinRequests\CompanyJoinRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCompanyJoinRequest extends ViewRecord
{
    protected static string $resource = CompanyJoinRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
