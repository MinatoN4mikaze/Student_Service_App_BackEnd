<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class UserProfileController extends Controller
{
    public function show()
    {
       $user = auth('sanctum')->user();

        // جلب بيانات الطالب أو الإداري حسب الدور
        if ($user->role === 'student') {
            $profile = [
                'name' => $user->name,
                'email' => $user->email,
                'year' => $user->student->year ?? null,
                'department' => $user->student->department ?? null,
                'profile_image' => $user->profile_image ?? null,
            ];
        } elseif ($user->role === 'admin') {
            $profile = [
                'name' => $user->name,
                'email' => $user->email,
                'position' => $user->admin->position ?? null,
                'profile_image' => $user->profile_image ?? null,
            ];
        }
        else {
            return response()->json(['message' => 'Role not supported'], 403);
        }

        return response()->json($profile);
    }

    public function update(Request $request)
    {
         $user = auth('sanctum')->user();

        // القواعد العامة للتحقق من البيانات
        $rules = [
            // 'name' => 'required|string|max:255',
            // 'email' => ['required','email', Rule::unique('users')->ignore($user->id)],
        "email"=>'required|email|unique:users,email',
            'profile_image' => 'nullable|image|max:2048', // صورة بحجم أقصى 2 ميجابايت
        ];

        // قواعد خاصة حسب الدور
        if ($user->role === 'student') {
            $rules['year'] = 'required|integer|min:1|max:10';
            $rules['department'] = 'required|string|max:255';
        } elseif ($user->role === 'admin') {
            $rules['position'] = 'required|string|max:255';
        } else {
            return response()->json(['message' => 'Role not supported'], 403);
        }

        $validated = $request->validate($rules);

        // تحديث بيانات المستخدم العامه
        $user->name = $validated['name'];
        $user->email = $validated['email'];

        // معالجة رفع الصورة
        if ($request->hasFile('profile_image')) {
            // حذف الصورة القديمة إذا وجدت
            if ($user->profile_image) {
                Storage::delete($user->profile_image);
            }
            $path = $request->file('profile_image')->store('profile_images');
            $user->profile_image = $path;
        }
        $user=User::find($user->id);
        $user->save();

        // تحديث بيانات الطالب أو الاداري حسب الدور
        if ($user->role === 'student') {
            $student = $user->student;
            $student->year = $validated['year'];
            $student->major = $validated['major'];
            $student->save();
        } elseif ($user->role === 'admin') {
            $admin = $user->admin;
            $admin->position = $validated['position'];
            $admin->save();
        }

        return response()->json(['message' => 'Profile updated successfully']);
    }
}
