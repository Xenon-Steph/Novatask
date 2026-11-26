<?php
namespace Tests\Feature;
use App\Models\{User,Task};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Carbon;
use Tests\TestCase;
class FocusSecurityTest extends TestCase {
 use RefreshDatabase;
 public function test_client_cannot_claim_arbitrary_focus_minutes(): void {
  $user=User::factory()->create();$this->actingAs($user);
  $id=$this->postJson('/api/tasks',['title'=>'Focus'])->assertCreated()->json('id');
  $this->postJson("/api/tasks/$id/focus",['minutes'=>9999])->assertNotFound();
  $session=$this->postJson("/api/tasks/$id/focus/start")->assertCreated()->json('id');
  $this->postJson("/api/tasks/$id/focus/$session/finish")->assertUnprocessable();
  $this->assertDatabaseHas('tasks',['id'=>$id,'focus_minutes'=>0]);
 }
}
