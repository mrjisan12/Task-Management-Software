<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('rule_key');
            $table->json('requirements')->nullable();
            $table->integer('points_reward')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['company_id', 'slug']);
        });

        Schema::create('user_badges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('badge_id')->constrained()->cascadeOnDelete();
            $table->timestamp('earned_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'user_id', 'badge_id']);
        });

        Schema::create('achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('icon')->nullable();
            $table->string('rule_key');
            $table->json('requirements')->nullable();
            $table->integer('points_reward')->default(0);
            $table->boolean('is_repeatable')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['company_id', 'slug']);
        });

        Schema::create('user_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('achievement_id')->constrained()->cascadeOnDelete();
            $table->timestamp('earned_at');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'user_id']);
            $table->unique(['company_id', 'user_id', 'achievement_id', 'earned_at'], 'user_achievement_repeatable_unique');
        });

        Schema::create('streaks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('current_streak')->default(0);
            $table->unsignedInteger('longest_streak')->default(0);
            $table->date('streak_started_on')->nullable();
            $table->date('last_activity_on')->nullable();
            $table->unsignedInteger('freeze_count')->default(0);
            $table->timestamps();

            $table->unique(['company_id', 'user_id']);
        });

        Schema::create('leaderboards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('scope_type')->default('company');
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->string('period')->default('monthly');
            $table->date('starts_on');
            $table->date('ends_on');
            $table->string('status')->default('active')->index();
            $table->timestamps();

            $table->unique(['company_id', 'scope_type', 'scope_id', 'period', 'starts_on'], 'leaderboard_period_unique');
        });

        Schema::create('leaderboard_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leaderboard_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('team_id')->nullable()->constrained()->cascadeOnDelete();
            $table->unsignedInteger('rank');
            $table->integer('points')->default(0);
            $table->unsignedInteger('tasks_completed')->default(0);
            $table->decimal('completion_rate', 5, 2)->default(0);
            $table->decimal('on_time_rate', 5, 2)->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['leaderboard_id', 'user_id', 'team_id'], 'leaderboard_participant_unique');
            $table->index(['leaderboard_id', 'rank']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leaderboard_entries');
        Schema::dropIfExists('leaderboards');
        Schema::dropIfExists('streaks');
        Schema::dropIfExists('user_achievements');
        Schema::dropIfExists('achievements');
        Schema::dropIfExists('user_badges');
        Schema::dropIfExists('badges');
    }
};
