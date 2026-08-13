<?php

namespace App\Filament\Resources\TaskPriorities\Pages;

use App\Filament\Resources\TaskPriorities\TaskPriorityResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTaskPriorities extends ListRecords
{
    protected static string $resource = TaskPriorityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
