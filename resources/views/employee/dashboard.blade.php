@php
    $xp = $pointSummary?->xp ?? 0;
    $nextXp = $nextLevel?->required_xp;
    $levelProgress = $nextXp ? min(100, (int) round(($xp / max($nextXp, 1)) * 100)) : 100;
    $describeRequirements = function (?array $requirements): string {
        if (! $requirements) {
            return 'Complete tasks and keep progressing.';
        }

        return collect($requirements)
            ->map(fn ($value, $key) => str($key)->headline().': '.$value)
            ->join(' · ');
    };
@endphp

<x-layouts.app>
    <div class="grid">
        <section class="panel dashboard-hero">
            <div class="hero-topline">
                <span class="hero-pill">{{ $company?->name ?? 'No active workspace' }}</span>
                @if ($monthlyRank)
                    <span class="hero-pill">Monthly rank #{{ $monthlyRank }}</span>
                @endif
            </div>

            <div>
                <h1 class="title">Good {{ now()->format('A') === 'AM' ? 'Morning' : 'Afternoon' }}, {{ $user->name }}</h1>
                <p class="subtitle">
                    @if ($company)
                        Your workspace is ready. Keep tasks moving, protect your streak, and climb the leaderboard.
                    @else
                        Join a company to unlock tasks, points, rankings, and team activity.
                    @endif
                </p>
            </div>

            <div class="hero-actions">
                @if ($company)
                    <a class="button" href="{{ route('tasks.create') }}">Create Task</a>
                    <a class="button secondary" href="{{ route('tasks.index') }}">View Tasks</a>
                @else
                    <span class="hero-pill">Use a company code to get started</span>
                @endif
            </div>
        </section>

        <section class="panel join-card">
            <h2 class="section-title">Join Company</h2>
            <p class="subtitle">Use your company code to request access.</p>

            <form method="POST" action="{{ route('company.join') }}">
                @csrf
                <div class="field">
                    <label class="label" for="code">Company Code</label>
                    <input class="input @error('code') error-input @enderror" id="code" name="code" placeholder="ilabs360" value="{{ old('code') }}" required>
                    @error('code') <div class="error">{{ $message }}</div> @enderror
                </div>
                <div class="field">
                    <button class="button full" type="submit">Join Company</button>
                </div>
            </form>
        </section>

        <section class="panel metric-card blue span-4">
            <div class="metric-icon">L</div>
            <div class="muted">Current Level</div>
            <div class="metric">{{ $currentLevel?->name ?? 'Level 1' }}</div>
            <p class="subtitle">
                {{ $xp }} XP
                @if ($nextLevel)
                    · next at {{ $nextLevel->required_xp }} XP
                @else
                    · top level reached
                @endif
            </p>
            <div class="progress-track" aria-hidden="true">
                <div class="progress-bar" style="width: {{ $levelProgress }}%;"></div>
            </div>
        </section>

        <section class="panel metric-card green span-4">
            <div class="metric-icon">P</div>
            <div class="muted">Current Points</div>
            <div class="metric">{{ $pointSummary?->total_points ?? 0 }}</div>
            <p class="subtitle">{{ $pointSummary?->monthly_points ?? 0 }} points this month.</p>
        </section>

        <section class="panel metric-card amber span-4">
            <div class="metric-icon">S</div>
            <div class="muted">Current Streak</div>
            <div class="metric">{{ $streak?->current_streak ?? 0 }} days</div>
            <p class="subtitle">
                Longest: {{ $streak?->longest_streak ?? 0 }} days
                @if ($monthlyRank)
                    · Monthly rank #{{ $monthlyRank }}
                @endif
            </p>
        </section>

        <section class="panel color-panel goals-panel span-12">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Future Goals</h2>
                    <p class="subtitle">See the levels, badges, and achievements your company has configured.</p>
                </div>
                <span class="badge">{{ $availableBadges->count() + $availableAchievements->count() }} goals</span>
            </div>

            @if ($company)
                <div class="goal-grid">
                    <div class="goal-card level-goal">
                        <div class="goal-icon">L</div>
                        <h3>{{ $nextLevel?->name ?? 'Top Level' }}</h3>
                        <p>
                            @if ($nextLevel)
                                Earn {{ max(0, $nextLevel->required_xp - $xp) }} more XP to reach this level.
                            @else
                                You have reached the highest configured level.
                            @endif
                        </p>
                        <div class="goal-meta">
                            <span class="badge">{{ $xp }} XP now</span>
                            @if ($nextLevel)
                                <span class="badge">{{ $nextLevel->required_xp }} XP target</span>
                            @endif
                        </div>
                    </div>

                    <div class="goal-card badge-goal">
                        <div class="goal-icon">B</div>
                        <h3>{{ $availableBadges->first()?->name ?? 'All Badges Earned' }}</h3>
                        <p>{{ $availableBadges->first()?->description ?? 'New badges will appear here when an admin creates them.' }}</p>
                        <div class="goal-meta">
                            <span class="badge">{{ $earnedBadges->count() }} earned</span>
                            <span class="badge">{{ $availableBadges->count() }} remaining</span>
                        </div>
                    </div>

                    <div class="goal-card achievement-goal">
                        <div class="goal-icon">A</div>
                        <h3>{{ $availableAchievements->first()?->name ?? 'All Achievements Earned' }}</h3>
                        <p>{{ $availableAchievements->first()?->description ?? 'New achievements will appear here when an admin creates them.' }}</p>
                        <div class="goal-meta">
                            <span class="badge">{{ $earnedAchievements->count() }} earned</span>
                            <span class="badge">{{ $availableAchievements->count() }} remaining</span>
                        </div>
                    </div>
                </div>

                <details class="goal-toggle">
                    <summary>View all future badges and achievements</summary>
                    <div class="goal-toggle-body">
                        <div class="goal-grid">
                            @forelse ($availableLevels as $level)
                                <div class="goal-card level-goal">
                                    <div class="goal-icon">{{ $level->icon ?: 'L' }}</div>
                                    <h3>{{ $level->name }}</h3>
                                    <p>{{ $level->description ?: 'Reach the required XP milestone.' }}</p>
                                    <div class="goal-meta">
                                        <span class="badge">{{ $level->required_xp }} XP</span>
                                        @if ($currentLevel?->id === $level->id)
                                            <span class="badge">Current</span>
                                        @elseif ($xp >= $level->required_xp)
                                            <span class="badge">Unlocked</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div class="goal-card level-goal">
                                    <div class="goal-icon">L</div>
                                    <h3>No Levels Configured</h3>
                                    <p>Super admin can add levels from the admin panel.</p>
                                </div>
                            @endforelse

                            @forelse ($availableBadges as $badge)
                                <div class="goal-card badge-goal">
                                    <div class="goal-icon">{{ $badge->icon ?: 'B' }}</div>
                                    <h3>{{ $badge->name }}</h3>
                                    <p>{{ $badge->description ?: 'Complete the configured badge rule.' }}</p>
                                    <div class="goal-meta">
                                        <span class="badge">{{ str($badge->rule_key)->headline() }}</span>
                                        <span class="badge">{{ $describeRequirements($badge->requirements) }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="goal-card badge-goal">
                                    <div class="goal-icon">B</div>
                                    <h3>No Future Badges</h3>
                                    <p>Super admin can add active badges from the admin panel.</p>
                                </div>
                            @endforelse

                            @forelse ($availableAchievements as $achievement)
                                <div class="goal-card achievement-goal">
                                    <div class="goal-icon">{{ $achievement->icon ?: 'A' }}</div>
                                    <h3>{{ $achievement->name }}</h3>
                                    <p>{{ $achievement->description ?: 'Complete the configured achievement rule.' }}</p>
                                    <div class="goal-meta">
                                        <span class="badge">{{ str($achievement->rule_key)->headline() }}</span>
                                        <span class="badge">{{ $describeRequirements($achievement->requirements) }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="goal-card achievement-goal">
                                    <div class="goal-icon">A</div>
                                    <h3>No Future Achievements</h3>
                                    <p>Super admin can add active achievements from the admin panel.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </details>
            @else
                <p class="subtitle">Join Ilabs360 with company code <strong>ilabs360</strong> to see your goals.</p>
            @endif
        </section>

        <section class="panel color-panel leaderboard-panel span-8">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Monthly Leaderboard</h2>
                    <p class="subtitle">Ranked by monthly points.</p>
                </div>
                @if ($monthlyRank)
                    <span class="badge">You are #{{ $monthlyRank }}</span>
                @endif
            </div>

            <div class="list">
                @forelse ($leaderboardEntries as $entry)
                    <div class="row">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <span class="rank-badge">#{{ $entry->rank }}</span>
                            <div>
                                <strong>{{ $entry->user?->name }}</strong>
                                <div class="muted">{{ $entry->tasks_completed }} completed tasks</div>
                            </div>
                        </div>
                        <span class="badge">{{ $entry->points }} pts</span>
                    </div>
                @empty
                    <p class="subtitle">Leaderboard will appear after task completions.</p>
                @endforelse
            </div>
        </section>

        <section class="panel color-panel badges-panel span-4">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Badges</h2>
                    <p class="subtitle">Recent unlocks.</p>
                </div>
                <span class="badge">{{ $earnedBadges->count() }}</span>
            </div>

            <div class="list">
                @forelse ($earnedBadges as $award)
                    <div class="row">
                        <div>
                            <strong>{{ $award->badge->name }}</strong>
                            <div class="muted">{{ $award->earned_at->format('M j') }}</div>
                        </div>
                        <span class="badge">{{ $award->badge->icon ?: 'Badge' }}</span>
                    </div>
                @empty
                    <p class="subtitle">No badges earned yet.</p>
                @endforelse
            </div>
        </section>

        <section class="panel color-panel achievements-panel span-12">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Recent Achievements</h2>
                    <p class="subtitle">Unlocked from your task completion patterns.</p>
                </div>
                <span class="badge">{{ $earnedAchievements->count() }} earned</span>
            </div>

            <div class="list">
                @forelse ($earnedAchievements as $award)
                    <div class="row">
                        <div>
                            <strong><span class="status-dot green"></span>{{ $award->achievement->name }}</strong>
                            <div class="muted">{{ $award->achievement->description }}</div>
                        </div>
                        <span class="badge">{{ $award->earned_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <p class="subtitle">Achievements unlock from completion patterns.</p>
                @endforelse
            </div>
        </section>

        <section class="panel color-panel task-panel span-12">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Task Snapshot</h2>
                    <p class="subtitle">Assigned work for the active company.</p>
                </div>
                @if ($company)
                    <a class="button" href="{{ route('tasks.create') }}">Create Task</a>
                @endif
            </div>

            <div class="mini-metrics">
                <div class="mini-metric pending">
                    <div class="muted">Pending Tasks</div>
                    <div class="metric">{{ $taskStats['pending'] }}</div>
                </div>
                <div class="mini-metric completed">
                    <div class="muted">Completed Tasks</div>
                    <div class="metric">{{ $taskStats['completed'] }}</div>
                </div>
                <div class="mini-metric overdue">
                    <div class="muted">Overdue Tasks</div>
                    <div class="metric">{{ $taskStats['overdue'] }}</div>
                </div>
            </div>

            <div class="list">
                @forelse ($recentTasks as $task)
                    <a class="row" href="{{ route('tasks.show', $task) }}">
                        <div>
                            <strong>{{ $task->title }}</strong>
                            <div class="muted">{{ $task->due_at ? 'Due '.$task->due_at->format('M j, g:i A') : 'No due date' }}</div>
                        </div>
                        <span class="badge">{{ $task->status->name }}</span>
                    </a>
                @empty
                    <p class="subtitle">No assigned tasks yet.</p>
                @endforelse
            </div>
        </section>

        <section class="panel span-8">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Company Memberships</h2>
                    <p class="subtitle">Switch between active workspaces.</p>
                </div>
            </div>

            <div class="list">
                @forelse ($memberships as $membership)
                    @php
                        $membershipBadgeClass = match ($membership->status) {
                            'active', 'approved' => 'success',
                            'pending' => 'warning',
                            'rejected', 'inactive' => 'danger',
                            default => 'neutral',
                        };
                    @endphp
                    <div class="row">
                        <div>
                            <strong>{{ $membership->company->name }}</strong>
                            <div class="muted">{{ $membership->title ?: 'Member' }}</div>
                        </div>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <span class="badge {{ $membershipBadgeClass }}">{{ str($membership->status)->headline() }}</span>
                            @if ($membership->status === 'active')
                                <form method="POST" action="{{ route('company.switch', $membership->company) }}">
                                    @csrf
                                    <button class="button secondary" type="submit">Use</button>
                                </form>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="subtitle">You have not joined a company yet.</p>
                @endforelse
            </div>
        </section>

        <section class="panel span-4">
            <div class="section-head">
                <div>
                    <h2 class="section-title">Join Requests</h2>
                    <p class="subtitle">Recent company access requests.</p>
                </div>
            </div>

            <div class="list">
                @forelse ($joinRequests as $joinRequest)
                    @php
                        $requestBadgeClass = match ($joinRequest->status) {
                            'active', 'approved' => 'success',
                            'pending' => 'warning',
                            'rejected', 'inactive' => 'danger',
                            default => 'neutral',
                        };
                    @endphp
                    <div class="row">
                        <div>
                            <strong>{{ $joinRequest->company->name }}</strong>
                            <div class="muted">{{ $joinRequest->created_at->diffForHumans() }}</div>
                        </div>
                        <span class="badge {{ $requestBadgeClass }}">{{ str($joinRequest->status)->headline() }}</span>
                    </div>
                @empty
                    <p class="subtitle">No pending join requests.</p>
                @endforelse
            </div>
        </section>
    </div>
</x-layouts.app>
