<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Workspace;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
class WorkspaceController extends Controller {
 private function membership(Request $request,Workspace $workspace,bool $admin=false): string {
  $role=$workspace->members()->where('users.id',$request->user()->id)->first()?->pivot?->role;
  abort_unless($role,404);
  if($admin) abort_unless(in_array($role,['owner','admin'],true),403);
  return $role;
 }
 public function index(Request $request){return $request->user()->belongsToMany(Workspace::class,'workspace_members')->withPivot('role')->orderBy('workspaces.name')->get()->map(fn($w)=>['id'=>$w->id,'name'=>$w->name,'role'=>$w->pivot->role]);}
 public function store(Request $request){$data=$request->validate(['name'=>'required|string|max:100']);return DB::transaction(function()use($request,$data){$w=Workspace::create(['owner_id'=>$request->user()->id,'name'=>$data['name']]);$w->members()->attach($request->user()->id,['role'=>'owner']);return response()->json(['id'=>$w->id,'name'=>$w->name,'role'=>'owner'],201);});}
 public function members(Request $request,Workspace $workspace){$this->membership($request,$workspace);return $workspace->members()->get(['users.id','users.name','users.email'])->map(fn($u)=>['id'=>$u->id,'name'=>$u->name,'email'=>$u->email,'role'=>$u->pivot->role]);}
 public function addMember(Request $request,Workspace $workspace){$this->membership($request,$workspace,true);$data=$request->validate(['email'=>'required|email|max:255','role'=>['required',Rule::in(['admin','member','viewer'])]]);
  $user=User::where('email',$data['email'])->first();abort_unless($user,422,'Cet utilisateur doit créer un compte avant de rejoindre l’équipe.');
  abort_if($workspace->members()->where('users.id',$user->id)->exists(),409,'Membre déjà présent');
  $workspace->members()->attach($user->id,['role'=>$data['role']]);DB::table('audit_events')->insert(['workspace_id'=>$workspace->id,'actor_id'=>$request->user()->id,'action'=>'member.added','subject_type'=>'user','subject_id'=>$user->id,'details'=>json_encode(['role'=>$data['role']]),'created_at'=>now(),'updated_at'=>now()]);return response()->json(['message'=>'Membre ajouté'],201);
 }
 public function removeMember(Request $request,Workspace $workspace,User $user){$role=$this->membership($request,$workspace,true);$member=$workspace->members()->where('users.id',$user->id)->first();abort_unless($member,404);abort_if($member->pivot->role==='owner',403,'Le propriétaire ne peut pas être retiré');if($role==='admin')abort_if($member->pivot->role==='admin',403);$workspace->members()->detach($user->id);return response()->noContent();}
}
