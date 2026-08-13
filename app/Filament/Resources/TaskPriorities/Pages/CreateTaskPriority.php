<?php

namespace App\Filament\Resources\TaskPriorities\Pages;

use App\Filament\Resources\TaskPriorities\TaskPriorityResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTaskPriority extends CreateRecord
{
    protected static string $resource = TaskPriorityResource::class;
}
