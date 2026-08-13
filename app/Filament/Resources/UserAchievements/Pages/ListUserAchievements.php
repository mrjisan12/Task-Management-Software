<?php

namespace App\Filament\Resources\UserAchievements\Pages;

use App\Filament\Resources\UserAchievements\UserAchievementResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserAchievements extends ListRecords
{
    protected static string $resource = UserAchievementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
