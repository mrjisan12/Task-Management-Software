<?php

namespace App\Filament\Resources\UserAchievements\Pages;

use App\Filament\Resources\UserAchievements\UserAchievementResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUserAchievement extends ViewRecord
{
    protected static string $resource = UserAchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
