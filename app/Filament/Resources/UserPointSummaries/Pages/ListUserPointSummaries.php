<?php

namespace App\Filament\Resources\UserPointSummaries\Pages;

use App\Filament\Resources\UserPointSummaries\UserPointSummaryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserPointSummaries extends ListRecords
{
    protected static string $resource = UserPointSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
