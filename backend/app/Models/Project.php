<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Project extends Model {
    protected $fillable = ['user_id','workspace_id','name','color'];
    public function tasks(): \Illuminate\Database\Eloquent\Relations\HasMany {return $this->hasMany(Task::class);}
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
