<?php

namespace App\Filament\Resources\TaskPriorities\Pages;

use App\Filament\Resources\TaskPriorities\TaskPriorityResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTaskPriority extends EditRecord
{
    protected static string $resource = TaskPriorityResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
