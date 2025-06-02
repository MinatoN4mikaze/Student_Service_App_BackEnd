<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ObjectionController;
use App\Models\Objection;
use App\Models\User;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ComplaintController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserProfileController;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');


Route::post('/login',[AuthController::class,'login']);
Route::post('/logout',[AuthController::class,'logout'])->middleware('auth:sanctum');


Route::middleware(['auth:sanctum'])->group(function () {
   //روابط الاعتراض
    Route::get('/objections', [ObjectionController::class, 'index']);
    Route::post('student/objections/{id}/submit', [ObjectionController::class, 'submit']);
    Route::get('admin/objections/submissions', [ObjectionController::class, 'allSubmissions']);
    Route::post('admin/objections', [ObjectionController::class, 'store']);
  //روابط الشكاوي
    Route::get('admin/complaints',[ComplaintController::class,'index']);
    Route::post('student/complaints',[ComplaintController::class,'store']);
    Route::delete('admin/complaints/{complaint}',[ComplaintController::class,'destroy']);
  
    //روابط الاعلانات
    Route::get('/announcements', [AnnouncementController::class, 'index']);
    Route::get('/announcements/{id}', [AnnouncementController::class, 'show']);
    Route::post('admin/announcements', [AnnouncementController::class, 'store']);
    Route::put('admin/announcements/{id}', [AnnouncementController::class, 'update']);
    Route::delete('admin/announcements/{id}', [AnnouncementController::class, 'destroy']);
      //رابط الاشعارات
    Route::get('/notifications', [NotificationController::class, 'notifications']);
    //روابط بروفايل 
    Route::get('/profile', [UserProfileController::class, 'show']);
    Route::post('/profile/update', [UserProfileController::class, 'update']);
});
