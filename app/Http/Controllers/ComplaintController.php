<?php

namespace App\Http\Controllers;

use App\Models\Complaint;
use COM;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ComplaintController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user=Auth::user();
        if($user->role!='admin')
        {
            return response()->json(['message'=>' عذرا لا تملك الصلاحيات '],403);
        }
        $complaints=Complaint::all();
        return response()->json(['complaints'=>$complaints]);
    }

    public function store(Request $request)
    {
        $user=Auth::user();
        if($user->role!='student')
        {
            return response()->json(['message'=>'عذرا لا تملك الصلاحيات لتقديم شكوى'],403);
        }
        $student_id=$user->student->student_id;
        $fields = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string|min:10'
        ]);

        Complaint::create(
            [
                'student_id'=>$student_id,
                'subject'=>$fields['subject'],
                'description'=>$fields['description'],
                ]
            );

            return response()->json(['message'=>'تم تسجيل الشكوى بنجاح '],200);

            // {
                // "id": 2,
                // "name": "Khaled Nour",
                // "email": "masdasdaa@gmail.com",
                // "unique_id": "87654321",
                // "role": "student",
                // "is_active": 1,
                // "email_verified_at": null,
                // "created_at": "2025-05-29T07:17:43.000000Z",
                // "updated_at": "2025-05-29T07:25:29.000000Z"
                // }

            }

            /**
             * Remove the specified resource from storage.
             */
    public function destroy(Complaint $complaint)
    {
        $user=Auth::user();
        if($user->role!='admin')
        {
            return response()->json(['message'=>' عذرا لا تملك الصلاحيات '],403);
        }
        $complaint->delete();
        return response()->json(['message'=>'تم حذف الشكوى بنجاح']);

    }
}
