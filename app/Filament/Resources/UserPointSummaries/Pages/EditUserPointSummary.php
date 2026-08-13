<?php

namespace App\Filament\Resources\UserPointSummaries\Pages;

use App\Filament\Resources\UserPointSummaries\UserPointSummaryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUserPointSummary extends EditRecord
{
    protected static string $resource = UserPointSummaryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
