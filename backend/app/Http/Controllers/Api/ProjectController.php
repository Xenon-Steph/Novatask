<?php
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Support\WorkspaceScope as Scope;
use Illuminate\Http\Request;
class ProjectController extends Controller {
 public function index(Request $request){$q=Project::query()->withCount('tasks');Scope::filter($q,$request);return $q->orderBy('name')->get();}
 public function store(Request $request){$space=Scope::current($request,true);$data=$request->validate(['name'=>'required|string|max:100','color'=>['sometimes','regex:/^#[0-9a-fA-F]{6}$/']]);return response()->json(Project::create($data+['user_id'=>$request->user()->id,'workspace_id'=>$space?->id]),201);}
 public function update(Request $request,Project $project){Scope::resource($request,$project,true);$project->update($request->validate(['name'=>'sometimes|required|string|max:100','color'=>['sometimes','regex:/^#[0-9a-fA-F]{6}$/']]));return $project;}
 public function destroy(Request $request,Project $project){Scope::resource($request,$project,true);$project->delete();return response()->noContent();}
}
