<?php
namespace App\Support;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
class WorkspaceScope {
 public static function current(Request $request,bool $write=false): ?Workspace {
  $id=$request->input('workspace_id');
  if($id===null || $id==='' || $id==='personal') return null;
  abort_unless(ctype_digit((string)$id) && (int)$id>0,422,'Espace invalide');
  $space=Workspace::findOrFail((int)$id);
  $role=$space->members()->where('users.id',$request->user()->id)->first()?->pivot?->role;
  abort_unless($role,404);
  if($write) abort_if($role==='viewer',403,'Accès en lecture seule');
  return $space;
 }
 public static function resource(Request $request,object $resource,bool $write=false): void {
  $space=self::current($request,$write);
  if($space) abort_unless((int)$resource->workspace_id===$space->id,404);
  else abort_unless($resource->workspace_id===null && (int)$resource->user_id===$request->user()->id,404);
 }
 public static function filter(Builder $query,Request $request,bool $write=false): ?Workspace {
  $space=self::current($request,$write);
  if($space) $query->where('workspace_id',$space->id);
  else $query->whereNull('workspace_id')->where('user_id',$request->user()->id);
  return $space;
 }
}
