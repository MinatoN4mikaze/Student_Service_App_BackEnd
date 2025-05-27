<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ObjectionController;
use App\Models\Objection;
use App\Models\User;
use App\Http\Controllers\AuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');




Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/objections', [ObjectionController::class, 'index']);
    Route::post('student/objections/{id}/submit', [ObjectionController::class, 'submit']);
    Route::get('admin/objections/submissions', [ObjectionController::class, 'allSubmissions']);
 Route::post('admin/objections', [ObjectionController::class, 'store']);




});


