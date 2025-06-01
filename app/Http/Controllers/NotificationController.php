<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    //
    public function notifications()
{
    $user = auth('sanctum')->user();
    return response()->json($user->notifications);
}
}
