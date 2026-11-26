<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Workspace extends Model {
 protected $fillable=['name','owner_id'];
 public function members(): BelongsToMany {return $this->belongsToMany(User::class,'workspace_members')->withPivot('role')->withTimestamps();}
}
