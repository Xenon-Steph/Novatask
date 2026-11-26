<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
class Task extends Model {
    protected $fillable = ['user_id','workspace_id','title','description','project_id','status','priority','due_at','estimate_minutes','position'];
    protected $casts = ['due_at'=>'datetime','completed_at'=>'datetime','estimate_minutes'=>'integer','focus_minutes'=>'integer'];
    public function project(): BelongsTo {return $this->belongsTo(Project::class);}
    public function subtasks(): HasMany {return $this->hasMany(Subtask::class)->orderBy('id');}
}
