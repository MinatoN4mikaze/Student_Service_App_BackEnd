<?php

namespace App\Http\Controllers;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
{
    $fields = $request->validate([
        'unique_id' => 'required|string',
        'password' => 'required|string|min:8',
    ]);

    $user = User::where('unique_id', $fields['unique_id'])->first();

    if (!$user) {
        return response()->json([
            'message' => 'المعرّف غير موجود'
        ], 404);
    }

    // أول دخول - الحساب غير مفعل
    if (!$user->is_active) {
        // تحقق إذا كان المستخدم لم يحدد كلمة مرور بعد
        if (empty($user->password)) {
            // في أول دخول، قم بتعيين كلمة المرور وتفعيل الحساب
            $user->password = Hash::make($fields['password']);
            $user->is_active = 1;
            $user->save();

            // إنشاء التوكن
            $user->tokens()->delete();
            $token = $user->createToken('auth_Token')->plainTextToken;

            return response()->json([
                "message" => "تم تفعيل الحساب وتعيين كلمة المرور بنجاح",
                "User" => $user,
                "Token" => $token,
            ], 200);
        } else {
            return response()->json([
                'message' => 'الحساب غير مفعل'
            ], 403);
        }
    }

    // الحساب مفعل، تحقق من كلمة المرور
    if (!Hash::check($fields['password'], $user->password)) {
        return response()->json([
            'message' => 'كلمة المرور غير صحيحة'
        ], 401);
    }

    // تسجيل الدخول
    $user->tokens()->delete();
    $token = $user->createToken('auth_Token')->plainTextToken;

    return response()->json([
        "message" => "تم تسجيل الدخول بنجاح",
        "User" => $user,
        "Token" => $token,
    ], 200);
}
 public function logout(Request $request)
    {
         $request->user()->currentAccessToken()->delete();

        return response()->json([
        'message' => 'تم تسجيل الخروج بنجاح.'
        ],201);
    }
}
