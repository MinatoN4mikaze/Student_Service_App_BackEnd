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
        $commonRules=[
            'unique_id'=>'required|string',
            'password'=>'required|string|min:8',
        ];
        $user=User::where('unique_id',$request->unique_id)->first();
        if(!$user)
        {
            return response()->json(['message'=>'المعرف غير موجود'],404);
        }
        if(!$user->is_active)
        {
            // $commonRules['password_cofirmation']='required|string|same:password';
            $commonRules['email'] = 'required|email|unique:users,email';
        }
        $fields=$request->validate($commonRules);
        if(!$user->is_active)
        {
            $user->password=$fields['password'];
            $user->email=$fields['email'];
            $user->is_active=1;
            $user->save();

            //create Token
            $user->tokens()->delete();
            $token = $user->createToken('auth_Token')->plainTextToken;
            return response()->json([
            "message" => "تم تفعيل الحساب وتعيين كلمة المرور بنجاح",
            "User" => $user,
            "Token" => $token,
            ], 200);

        }
        //هون سوي اذا كان الايميل غلط
        if (!Hash::check($fields['password'], $user->password))
        {
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

public function changePassword(Request $request)
{
    $user = auth('sanctum')->user();

    $request->validate([
        'current_password' => 'required|string',
        'new_password' => 'required|string|min:8|confirmed',
    ]);

    // التأكد من أن كلمة المرور الحالية صحيحة
    if (!Hash::check($request->current_password, $user->password)) {
        return response()->json(['message' => 'كلمة المرور الحالية غير صحيحة'], 401);
    }

    // تغيير كلمة المرور
    $user->password = bcrypt($request->new_password);
    $user=User::find($user->id);
    $user->save();

    return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح']);
}


}
