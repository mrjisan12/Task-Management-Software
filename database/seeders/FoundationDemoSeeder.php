<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Achievement;
use App\Models\Badge;
use App\Models\Department;
use App\Models\Level;
use App\Models\PointRule;
use App\Models\Task;
use App\Models\TaskCategory;
use App\Models\TaskPriority;
use App\Models\TaskStatus;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Services\GamificationProgressService;
use App\Services\PointService;

class FoundationDemoSeeder extends Seeder
{
    public function run(): void
    {
        $superAdmin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        );
        $superAdmin->assignRole('super_admin');

        $abc = Company::query()->firstOrCreate(
            ['slug' => 'abc-technologies'],
            [
                'name' => 'ABC Technologies Ltd.',
                'code' => 'ABC-7K29X',
                'join_mode' => 'approval_required',
                'status' => 'active',
            ],
        );

        $xyz = Company::query()->firstOrCreate(
            ['slug' => 'xyz-solutions'],
            [
                'name' => 'XYZ Solutions',
                'code' => 'XYZ-4M81Q',
                'join_mode' => 'open',
                'status' => 'active',
            ],
        );

        $employees = [
            ['Rahim', 'rahim@example.com', 'manager'],
            ['Jisan', 'jisan@example.com', 'employee'],
            ['Karim', 'karim@example.com', 'team_lead'],
            ['Nabila', 'nabila@example.com', 'employee'],
            ['Hasan', 'hasan@example.com', 'employee'],
        ];

        foreach ($employees as [$name, $email, $role]) {
            $user = User::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                    'is_active' => true,
                ],
            );

            $user->assignRole($role);

            $abc->memberships()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'status' => 'active',
                    'title' => Str::headline($role),
                    'joined_at' => now(),
                    'approved_by' => $superAdmin->id,
                    'approved_at' => now(),
                ],
            );
        }

        $superAdmin->companyMemberships()->updateOrCreate(
            ['company_id' => $abc->id],
            [
                'status' => 'active',
                'title' => 'Platform Owner',
                'joined_at' => now(),
                'approved_at' => now(),
            ],
        );

        foreach (['Backend', 'Frontend', 'HR'] as $departmentName) {
            Department::query()->firstOrCreate(
                ['company_id' => $abc->id, 'name' => $departmentName],
                ['is_active' => true],
            );
        }

        $backend = Team::query()->firstOrCreate(
            ['company_id' => $abc->id, 'name' => 'Backend Team'],
            [
                'department_id' => Department::query()->where('company_id', $abc->id)->where('name', 'Backend')->value('id'),
                'lead_user_id' => User::query()->where('email', 'karim@example.com')->value('id'),
                'is_active' => true,
            ],
        );

        $frontend = Team::query()->firstOrCreate(
            ['company_id' => $abc->id, 'name' => 'Frontend Team'],
            [
                'department_id' => Department::query()->where('company_id', $abc->id)->where('name', 'Frontend')->value('id'),
                'is_active' => true,
            ],
        );

        foreach (['rahim@example.com', 'jisan@example.com', 'karim@example.com'] as $email) {
            $backend->memberships()->updateOrCreate(
                ['user_id' => User::query()->where('email', $email)->value('id')],
                ['role' => $email === 'karim@example.com' ? 'lead' : 'member', 'joined_at' => now()],
            );
        }

        foreach (['nabila@example.com', 'hasan@example.com'] as $email) {
            $frontend->memberships()->updateOrCreate(
                ['user_id' => User::query()->where('email', $email)->value('id')],
                ['role' => 'member', 'joined_at' => now()],
            );
        }

        $xyz->memberships()->updateOrCreate(
            ['user_id' => $superAdmin->id],
            [
                'status' => 'active',
                'title' => 'Platform Owner',
                'joined_at' => now(),
                'approved_at' => now(),
            ],
        );

        $this->seedTaskConfiguration($abc);
        $this->seedGamificationConfiguration($abc);
        $this->seedProgressConfiguration($abc);
        $this->seedDemoTasks($abc);
        $this->seedTaskConfiguration($xyz);
        $this->seedGamificationConfiguration($xyz);
        $this->seedProgressConfiguration($xyz);
    }

    private function seedTaskConfiguration(Company $company): void
    {
        $statuses = [
            ['Pending', 'pending', 'gray', 10, false],
            ['In Progress', 'in-progress', 'blue', 20, false],
            ['Completed', 'completed', 'green', 30, true],
            ['Cancelled', 'cancelled', 'red', 40, true],
            ['Overdue', 'overdue', 'orange', 50, false],
        ];

        foreach ($statuses as [$name, $slug, $color, $sortOrder, $isTerminal]) {
            TaskStatus::query()->updateOrCreate(
                ['company_id' => $company->id, 'slug' => $slug],
                [
                    'name' => $name,
                    'color' => $color,
                    'sort_order' => $sortOrder,
                    'is_terminal' => $isTerminal,
                    'is_active' => true,
                ],
            );
        }

        $priorities = [
            ['Critical', 'critical', 40, 'red', 10],
            ['High', 'high', 30, 'orange', 20],
            ['Medium', 'medium', 20, 'blue', 30],
            ['Low', 'low', 10, 'gray', 40],
        ];

        foreach ($priorities as [$name, $slug, $weight, $color, $sortOrder]) {
            TaskPriority::query()->updateOrCreate(
                ['company_id' => $company->id, 'slug' => $slug],
                [
                    'name' => $name,
                    'weight' => $weight,
                    'color' => $color,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ],
            );
        }

        foreach (['Development', 'Bug Fix', 'Documentation', 'Operations'] as $categoryName) {
            TaskCategory::query()->updateOrCreate(
                ['company_id' => $company->id, 'slug' => Str::slug($categoryName)],
                [
                    'name' => $categoryName,
                    'color' => 'gray',
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedDemoTasks(Company $company): void
    {
        $pending = TaskStatus::query()->where('company_id', $company->id)->where('slug', 'pending')->first();
        $inProgress = TaskStatus::query()->where('company_id', $company->id)->where('slug', 'in-progress')->first();
        $completed = TaskStatus::query()->where('company_id', $company->id)->where('slug', 'completed')->first();
        $high = TaskPriority::query()->where('company_id', $company->id)->where('slug', 'high')->first();
        $medium = TaskPriority::query()->where('company_id', $company->id)->where('slug', 'medium')->first();
        $development = TaskCategory::query()->where('company_id', $company->id)->where('slug', 'development')->first();
        $bugFix = TaskCategory::query()->where('company_id', $company->id)->where('slug', 'bug-fix')->first();

        $rahim = User::query()->where('email', 'rahim@example.com')->first();
        $jisan = User::query()->where('email', 'jisan@example.com')->first();
        $karim = User::query()->where('email', 'karim@example.com')->first();
        $backend = Team::query()->where('company_id', $company->id)->where('name', 'Backend Team')->first();

        $task = Task::query()->updateOrCreate(
            ['company_id' => $company->id, 'title' => 'Fix Payment Gateway'],
            [
                'created_by' => $rahim->id,
                'team_id' => $backend->id,
                'task_status_id' => $pending->id,
                'task_priority_id' => $high->id,
                'task_category_id' => $bugFix->id,
                'description' => 'Investigate failed payment callbacks and patch the gateway handler.',
                'due_at' => now()->addDay(),
                'estimated_minutes' => 180,
            ],
        );
        $task->assignments()->updateOrCreate(
            ['assignee_user_id' => $jisan->id],
            ['assigned_by' => $rahim->id, 'status' => 'assigned', 'assigned_at' => now()],
        );

        $task = Task::query()->updateOrCreate(
            ['company_id' => $company->id, 'title' => 'Document API Error Codes'],
            [
                'created_by' => $karim->id,
                'team_id' => $backend->id,
                'task_status_id' => $inProgress->id,
                'task_priority_id' => $medium->id,
                'task_category_id' => $development->id,
                'description' => 'Create a short internal reference for common API errors.',
                'due_at' => now()->addDays(2),
                'estimated_minutes' => 90,
            ],
        );
        $task->assignments()->updateOrCreate(
            ['assignee_team_id' => $backend->id],
            ['assigned_by' => $karim->id, 'status' => 'assigned', 'assigned_at' => now()],
        );

        $task = Task::query()->updateOrCreate(
            ['company_id' => $company->id, 'title' => 'Clean Up Old Login Logs'],
            [
                'created_by' => $rahim->id,
                'team_id' => $backend->id,
                'task_status_id' => $completed->id,
                'task_priority_id' => $medium->id,
                'task_category_id' => $development->id,
                'description' => 'Archive older login diagnostic records from the staging system.',
                'due_at' => now()->subDay(),
                'estimated_minutes' => 60,
                'completed_by' => $jisan->id,
                'completed_at' => now()->subHours(4),
                'completion_comment' => 'Archived staging logs and confirmed disk usage dropped.',
            ],
        );
        $task->assignments()->updateOrCreate(
            ['assignee_user_id' => $jisan->id],
            ['assigned_by' => $rahim->id, 'status' => 'completed', 'assigned_at' => now()->subDays(2), 'completed_at' => now()->subHours(4)],
        );

        app(PointService::class)->awardForRule(
            company: $company,
            user: $jisan,
            ruleKey: 'task_completed',
            source: 'demo_seed',
            sourceId: (string) $task->id,
            task: $task,
            description: 'Task completed: '.$task->title,
            idempotencyKey: "demo:task:{$task->id}:completed:user:{$jisan->id}",
        );

        app(PointService::class)->awardForRule(
            company: $company,
            user: $rahim,
            ruleKey: 'task_assignment_success',
            source: 'demo_seed',
            sourceId: (string) $task->id,
            task: $task,
            description: 'Assigned task completed: '.$task->title,
            idempotencyKey: "demo:task:{$task->id}:assignment-success:user:{$rahim->id}",
        );

        app(PointService::class)->incrementTasksCompleted($company, $jisan, $task);
        app(GamificationProgressService::class)->handleTaskCompleted($task->refresh(), $jisan);
    }

    private function seedGamificationConfiguration(Company $company): void
    {
        $rules = [
            ['task_completed', 'Task Completed', 5],
            ['task_assignment_success', 'Assigned Task Completed', 2],
            ['on_time_bonus', 'On-Time Bonus', 2],
            ['early_completion_bonus', 'Early Completion Bonus', 1],
            ['lucky_bonus', 'Lucky Bonus', 3],
            ['reward_redemption', 'Reward Redemption', -100],
            ['point_reversal', 'Point Reversal', 0],
        ];

        foreach ($rules as [$key, $name, $points]) {
            PointRule::query()->updateOrCreate(
                ['company_id' => $company->id, 'key' => $key],
                [
                    'name' => $name,
                    'points' => $points,
                    'conditions' => null,
                    'is_active' => true,
                ],
            );
        }

        $levels = [
            ['Beginner', 0, 'L1', 'Getting started with productive work.', 10],
            ['Task Fighter', 100, 'L2', 'Consistently completing assigned work.', 20],
            ['Task Warrior', 250, 'L3', 'Strong monthly productivity momentum.', 30],
            ['Productivity Pro', 500, 'L4', 'High-impact contributor.', 40],
            ['Office Legend', 1000, 'L5', 'Elite productivity and consistency.', 50],
        ];

        foreach ($levels as [$name, $requiredXp, $icon, $description, $sortOrder]) {
            Level::query()->updateOrCreate(
                ['company_id' => $company->id, 'required_xp' => $requiredXp],
                [
                    'name' => $name,
                    'icon' => $icon,
                    'description' => $description,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ],
            );
        }
    }

    private function seedProgressConfiguration(Company $company): void
    {
        $badges = [
            ['Task Rookie', 'task-rookie', 'First completed task.', 'B1', 'tasks_completed_count', ['threshold' => 1]],
            ['Task Warrior', 'task-warrior', 'Complete 10 tasks.', 'B2', 'tasks_completed_count', ['threshold' => 10]],
            ['Speed Demon', 'speed-demon', 'Complete 5 tasks on time.', 'B3', 'on_time_tasks_count', ['threshold' => 5]],
            ['Unstoppable', 'unstoppable', 'Maintain a 7 day streak.', 'B4', 'streak_days', ['threshold' => 7]],
            ['Productivity Hero', 'productivity-hero', 'Earn 150 points in a month.', 'B5', 'monthly_points', ['threshold' => 150]],
        ];

        foreach ($badges as [$name, $slug, $description, $icon, $ruleKey, $requirements]) {
            Badge::query()->updateOrCreate(
                ['company_id' => $company->id, 'slug' => $slug],
                [
                    'name' => $name,
                    'description' => $description,
                    'icon' => $icon,
                    'rule_key' => $ruleKey,
                    'requirements' => $requirements,
                    'points_reward' => 0,
                    'is_active' => true,
                ],
            );
        }

        $achievements = [
            ['Deadline Sprinter', 'deadline-sprinter', 'Completed a task shortly before the deadline.', 'A1', 'deadline_sprinter', ['minutes_before_due' => 60], true],
            ['Why So Slow?', 'why-so-slow', 'Completed a task very late.', 'A2', 'very_late_completion', ['hours_late' => 24], true],
            ['Task Burst', 'task-burst', 'Completed 3 tasks in one day.', 'A3', 'daily_task_burst', ['threshold' => 3], false],
        ];

        foreach ($achievements as [$name, $slug, $description, $icon, $ruleKey, $requirements, $repeatable]) {
            Achievement::query()->updateOrCreate(
                ['company_id' => $company->id, 'slug' => $slug],
                [
                    'name' => $name,
                    'description' => $description,
                    'icon' => $icon,
                    'rule_key' => $ruleKey,
                    'requirements' => $requirements,
                    'points_reward' => 0,
                    'is_repeatable' => $repeatable,
                    'is_active' => true,
                ],
            );
        }
    }
}
