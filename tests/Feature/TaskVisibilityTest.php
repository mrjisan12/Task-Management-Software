<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Task;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\User;
use App\Support\CompanyContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskVisibilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (! extension_loaded('pdo_sqlite')) {
            $this->markTestSkipped('The pdo_sqlite extension is required for in-memory database feature tests.');
        }

        parent::setUp();
    }

    public function test_sent_tab_only_shows_incomplete_tasks_created_by_the_user(): void
    {
        [$creator, $assignee, $company, $pendingStatus, $completedStatus, $priority] = $this->taskFixtures();

        $incompleteTask = Task::query()->create([
            'company_id' => $company->id,
            'created_by' => $creator->id,
            'task_status_id' => $pendingStatus->id,
            'task_priority_id' => $priority->id,
            'title' => 'Sent incomplete task',
        ]);

        $completedTask = Task::query()->create([
            'company_id' => $company->id,
            'created_by' => $creator->id,
            'task_status_id' => $completedStatus->id,
            'task_priority_id' => $priority->id,
            'completed_by' => $assignee->id,
            'completed_at' => now(),
            'title' => 'Sent completed task',
        ]);

        foreach ([$incompleteTask, $completedTask] as $task) {
            $task->assignments()->create([
                'assignee_user_id' => $assignee->id,
                'assigned_by' => $creator->id,
                'status' => $task->is($completedTask) ? 'completed' : 'assigned',
                'assigned_at' => now(),
                'completed_at' => $task->is($completedTask) ? now() : null,
            ]);
        }

        $response = $this
            ->actingAs($creator)
            ->withSession([CompanyContext::SESSION_KEY => $company->id])
            ->get(route('tasks.index'));

        $response->assertOk();
        $response->assertSee('Sent incomplete task');
        $response->assertDontSee('Sent completed task');
    }

    public function test_completed_tasks_cannot_be_deleted(): void
    {
        [$creator, $assignee, $company, $pendingStatus, $completedStatus, $priority] = $this->taskFixtures();

        $task = Task::query()->create([
            'company_id' => $company->id,
            'created_by' => $creator->id,
            'task_status_id' => $completedStatus->id,
            'task_priority_id' => $priority->id,
            'completed_by' => $assignee->id,
            'completed_at' => now(),
            'title' => 'Completed protected task',
        ]);

        $response = $this
            ->actingAs($creator)
            ->withSession([CompanyContext::SESSION_KEY => $company->id])
            ->delete(route('tasks.destroy', $task));

        $response->assertForbidden();
        $this->assertNotSoftDeleted($task);
    }

    private function taskFixtures(): array
    {
        $creator = User::factory()->create();
        $assignee = User::factory()->create();
        $company = Company::query()->create([
            'name' => 'Task Company',
            'slug' => 'task-company',
            'code' => 'TSK-12345',
            'join_mode' => 'open',
            'status' => 'active',
        ]);

        foreach ([$creator, $assignee] as $user) {
            $company->memberships()->create([
                'user_id' => $user->id,
                'status' => 'active',
                'joined_at' => now(),
            ]);
        }

        $pendingStatus = TaskStatus::query()->create([
            'name' => 'Pending',
            'slug' => 'pending',
            'color' => 'yellow',
            'sort_order' => 1,
            'is_terminal' => false,
            'is_active' => true,
        ]);

        $completedStatus = TaskStatus::query()->create([
            'name' => 'Completed',
            'slug' => 'completed',
            'color' => 'green',
            'sort_order' => 2,
            'is_terminal' => true,
            'is_active' => true,
        ]);

        $priority = TaskPriority::query()->create([
            'name' => 'Medium',
            'slug' => 'medium',
            'weight' => 50,
            'color' => 'blue',
            'sort_order' => 1,
            'is_active' => true,
        ]);

        return [$creator, $assignee, $company, $pendingStatus, $completedStatus, $priority];
    }
}
