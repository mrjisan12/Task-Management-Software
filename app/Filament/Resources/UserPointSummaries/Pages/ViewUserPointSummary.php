<?php

namespace App\Filament\Resources\UserPointSummaries\Pages;

use App\Filament\Resources\UserPointSummaries\UserPointSummaryResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUserPointSummary extends ViewRecord
{
    protected static string $resource = UserPointSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
