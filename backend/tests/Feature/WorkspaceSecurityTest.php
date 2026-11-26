<?php
namespace Tests\Feature;
use App\Models\{User,Workspace,Task};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class WorkspaceSecurityTest extends TestCase {
 use RefreshDatabase;
 public function test_personal_tasks_are_isolated(): void {
  $a=User::factory()->create();$b=User::factory()->create();
  $this->actingAs($a)->postJson('/api/tasks',['title'=>'Privée'])->assertCreated();
  $this->actingAs($b)->getJson('/api/tasks')->assertOk()->assertJsonCount(0,'data');
  $this->actingAs($b)->patchJson('/api/tasks/1',['title'=>'Volée'])->assertNotFound();
 }
 public function test_viewers_can_read_but_not_write_team_tasks(): void {
  $owner=User::factory()->create();$viewer=User::factory()->create();
  $w=Workspace::create(['name'=>'Équipe','owner_id'=>$owner->id]);$w->members()->attach($owner->id,['role'=>'owner']);$w->members()->attach($viewer->id,['role'=>'viewer']);
  $this->actingAs($owner)->postJson('/api/tasks?workspace_id='.$w->id,['title'=>'Partagée'])->assertCreated();
  $this->actingAs($viewer)->getJson('/api/tasks?workspace_id='.$w->id)->assertOk()->assertJsonCount(1,'data');
  $this->actingAs($viewer)->postJson('/api/tasks?workspace_id='.$w->id,['title'=>'Interdit'])->assertForbidden();
  $this->actingAs($viewer)->patchJson('/api/tasks/1?workspace_id='.$w->id,['title'=>'Interdit'])->assertForbidden();
 }
 public function test_non_members_cannot_access_team_tasks(): void {
  $owner=User::factory()->create();$intruder=User::factory()->create();
  $w=Workspace::create(['name'=>'Secret','owner_id'=>$owner->id]);$w->members()->attach($owner->id,['role'=>'owner']);
  $this->actingAs($owner)->postJson('/api/tasks?workspace_id='.$w->id,['title'=>'Secret'])->assertCreated();
  $this->actingAs($intruder)->getJson('/api/tasks?workspace_id='.$w->id)->assertNotFound();
  $this->actingAs($intruder)->deleteJson('/api/tasks/1?workspace_id='.$w->id)->assertNotFound();
 }
 public function test_project_cannot_be_used_across_workspaces(): void {
  $a=User::factory()->create();$b=User::factory()->create();
  $project=$this->actingAs($a)->postJson('/api/projects',['name'=>'A'])->assertCreated()->json('id');
  $this->actingAs($b)->postJson('/api/tasks',['title'=>'Test','project_id'=>$project])->assertNotFound();
 }
 public function test_ai_requires_server_configuration(): void {
  config()->set('services.novatask_ai.key',null);config()->set('services.novatask_ai.model',null);
  $this->actingAs(User::factory()->create())->postJson('/api/ai/plan',['goal'=>'Préparer un examen de mathématiques'])->assertStatus(503)->assertJson(['configured'=>false]);
 }
}
