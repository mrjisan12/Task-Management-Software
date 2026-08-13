<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('point_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('name');
            $table->integer('points');
            $table->json('conditions')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['company_id', 'key']);
        });

        Schema::create('point_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('task_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('point_rule_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('reversed_transaction_id')->nullable()->constrained('point_transactions')->nullOnDelete();
            $table->string('type');
            $table->string('source');
            $table->string('source_id')->nullable();
            $table->string('idempotency_key')->unique();
            $table->integer('points');
            $table->string('description');
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['company_id', 'user_id', 'created_at']);
            $table->index('task_id');
            $table->index(['source', 'source_id']);
        });

        Schema::create('user_point_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->integer('total_points')->default(0);
            $table->integer('monthly_points')->default(0);
            $table->integer('xp')->default(0);
            $table->unsignedInteger('tasks_completed')->default(0);
            $table->timestamp('last_recalculated_at')->nullable();
            $table->timestamps();

            $table->unique(['company_id', 'user_id']);
        });

        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unsignedInteger('required_xp');
            $table->string('icon')->nullable();
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['company_id', 'required_xp']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('levels');
        Schema::dropIfExists('user_point_summaries');
        Schema::dropIfExists('point_transactions');
        Schema::dropIfExists('point_rules');
    }
};
