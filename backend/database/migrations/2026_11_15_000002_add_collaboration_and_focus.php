<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up(): void {
  Schema::create('workspaces',function(Blueprint $t){$t->id();$t->foreignId('owner_id')->constrained('users')->cascadeOnDelete();$t->string('name',100);$t->timestamps();});
  Schema::create('workspace_members',function(Blueprint $t){$t->id();$t->foreignId('workspace_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->enum('role',['owner','admin','member','viewer']);$t->timestamps();$t->unique(['workspace_id','user_id']);});
  Schema::table('projects',function(Blueprint $t){$t->foreignId('workspace_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();$t->index(['workspace_id','name']);});
  Schema::table('tasks',function(Blueprint $t){$t->foreignId('workspace_id')->nullable()->after('user_id')->constrained()->cascadeOnDelete();$t->index(['workspace_id','status','position']);});
  Schema::create('focus_sessions',function(Blueprint $t){$t->id();$t->foreignId('task_id')->constrained()->cascadeOnDelete();$t->foreignId('user_id')->constrained()->cascadeOnDelete();$t->timestamp('started_at');$t->timestamp('ended_at')->nullable();$t->unsignedSmallInteger('credited_minutes')->default(0);$t->timestamps();$t->index(['user_id','ended_at']);});
  Schema::create('audit_events',function(Blueprint $t){$t->id();$t->foreignId('workspace_id')->nullable()->constrained()->cascadeOnDelete();$t->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();$t->string('action',70);$t->string('subject_type',40);$t->unsignedBigInteger('subject_id');$t->json('details')->nullable();$t->timestamps();$t->index(['workspace_id','created_at']);});
 }
 public function down(): void {Schema::dropIfExists('audit_events');Schema::dropIfExists('focus_sessions');Schema::table('tasks',fn(Blueprint $t)=>$t->dropConstrainedForeignId('workspace_id'));Schema::table('projects',fn(Blueprint $t)=>$t->dropConstrainedForeignId('workspace_id'));Schema::dropIfExists('workspace_members');Schema::dropIfExists('workspaces');}
};
