<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\{AuthController,ProjectController,TaskController,WorkspaceController,AiController};
Route::middleware('auth:sanctum')->group(function () {
 Route::get('/me',[AuthController::class,'me']);
 Route::get('/workspaces',[WorkspaceController::class,'index']);
 Route::post('/workspaces',[WorkspaceController::class,'store'])->middleware('throttle:20,1');
 Route::get('/workspaces/{workspace}/members',[WorkspaceController::class,'members']);
 Route::post('/workspaces/{workspace}/members',[WorkspaceController::class,'addMember'])->middleware('throttle:10,1');
 Route::delete('/workspaces/{workspace}/members/{user}',[WorkspaceController::class,'removeMember']);
 Route::post('/ai/plan',[AiController::class,'plan'])->middleware('throttle:6,1');
 Route::apiResource('projects',ProjectController::class)->except(['show']);
 Route::get('/stats',[TaskController::class,'stats']);
 Route::post('/tasks/{task}/focus/start',[TaskController::class,'focusStart']);
 Route::post('/tasks/{task}/focus/{session}/finish',[TaskController::class,'focusFinish']);
 Route::post('/tasks/{task}/subtasks',[TaskController::class,'addSubtask']);
 Route::patch('/tasks/{task}/subtasks/{subtask}',[TaskController::class,'updateSubtask']);
 Route::delete('/tasks/{task}/subtasks/{subtask}',[TaskController::class,'deleteSubtask']);
 Route::apiResource('tasks',TaskController::class)->except(['show']);
});
