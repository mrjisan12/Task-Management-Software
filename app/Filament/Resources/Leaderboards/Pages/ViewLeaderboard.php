<?php

namespace App\Filament\Resources\Leaderboards\Pages;

use App\Filament\Resources\Leaderboards\LeaderboardResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewLeaderboard extends ViewRecord
{
    protected static string $resource = LeaderboardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
