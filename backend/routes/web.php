<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
Route::middleware('throttle:10,1')->group(function () {
    Route::post('/register',[AuthController::class,'register']);
    Route::post('/login',[AuthController::class,'login']);
});
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth');
