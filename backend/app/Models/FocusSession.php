<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class FocusSession extends Model {
 protected $fillable=['task_id','user_id','started_at'];
 protected $casts=['started_at'=>'datetime','ended_at'=>'datetime'];
}
