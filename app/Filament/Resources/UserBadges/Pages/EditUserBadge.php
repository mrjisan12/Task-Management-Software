<?php

namespace App\Filament\Resources\UserBadges\Pages;

use App\Filament\Resources\UserBadges\UserBadgeResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditUserBadge extends EditRecord
{
    protected static string $resource = UserBadgeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}
