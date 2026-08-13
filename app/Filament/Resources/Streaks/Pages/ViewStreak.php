<?php

namespace App\Filament\Resources\Streaks\Pages;

use App\Filament\Resources\Streaks\StreakResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewStreak extends ViewRecord
{
    protected static string $resource = StreakResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
