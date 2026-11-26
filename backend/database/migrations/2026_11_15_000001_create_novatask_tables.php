<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('projects', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 100); $table->string('color', 7)->default('#8b5cf6');
            $table->timestamps(); $table->index(['user_id', 'name']);
        });
        Schema::create('tasks', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title', 200); $table->text('description')->nullable();
            $table->enum('status', ['todo', 'progress', 'done'])->default('todo');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->dateTime('due_at')->nullable(); $table->dateTime('completed_at')->nullable();
            $table->unsignedInteger('estimate_minutes')->nullable();
            $table->unsignedInteger('focus_minutes')->default(0);
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();
            $table->index(['user_id','status','position']);
            $table->index(['user_id','due_at']);
            $table->index(['user_id','project_id']);
        });
        Schema::create('subtasks', function (Blueprint $table) {
            $table->id(); $table->foreignId('task_id')->constrained()->cascadeOnDelete();
            $table->string('title',200); $table->boolean('is_done')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('subtasks'); Schema::dropIfExists('tasks'); Schema::dropIfExists('projects');
    }
};
