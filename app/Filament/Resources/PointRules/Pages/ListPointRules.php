<?php

namespace App\Filament\Resources\PointRules\Pages;

use App\Filament\Resources\PointRules\PointRuleResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPointRules extends ListRecords
{
    protected static string $resource = PointRuleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
