<?php

namespace App\Filament\Resources\CompanyJoinRequests\Pages;

use App\Filament\Resources\CompanyJoinRequests\CompanyJoinRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCompanyJoinRequest extends EditRecord
{
    protected static string $resource = CompanyJoinRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
