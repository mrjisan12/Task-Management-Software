<?php

namespace App\Filament\Resources\PointRules\Pages;

use App\Filament\Resources\PointRules\PointRuleResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPointRule extends EditRecord
{
    protected static string $resource = PointRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
