<?php

namespace App\Http\Controllers;

use App\Models\Objection;
use App\Models\StudentObjection;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class AdminObjectionController extends Controller
{
    // 1️⃣ حذف طلب الاعتراض
    public function deleteRequest($submissionId)
    {
        $user = auth('sanctum')->user();
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $submission = StudentObjection::find($submissionId);
        if (!$submission) {
            return response()->json(['message' => 'طلب الاعتراض غير موجود'], 404);
        }

        $submission->delete();

        return response()->json(['message' => 'تم حذف طلب الاعتراض بنجاح']);
    }

    // 2️⃣ قبول الاعتراض وتحديث العلامة
    public function acceptRequest(Request $request, $submissionId)
    {
        $user = auth('sanctum')->user();
        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        // if ($user->role !== 'admin' && $user->role !== 'student affairs') {
        //     return response()->json(['message' => 'Unauthorized'], 403);
        // }

        $validator = Validator::make($request->all(), [
            'new_grade' => 'required|numeric|min:0|max:100',
        ], [
            'new_grade.required' => 'العلامة بعد التعديل مطلوبة',
            'new_grade.numeric' => 'العلامة يجب أن تكون عدد',
            'new_grade.min' => 'العلامة يجب أن تكون بين 0 و 100',
            'new_grade.max' => 'العلامة يجب أن تكون بين 0 و 100',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $submission = StudentObjection::find($submissionId);
        if (!$submission) {
            return response()->json(['message' => 'طلب الاعتراض غير موجود'], 404);
        }

        $submission->accepted = true;
        $submission->new_grade = $request->new_grade;
        $submission->save();

        return response()->json([
            'message' => 'تم قبول الاعتراض وتحديث العلامة',
            'submission' => $submission
        ]);
    }

    // 3️⃣ جلب الطلبات المقبولة لمادة معينة

public function acceptedRequestsBySubject($subject_name)
{
    $user = auth('sanctum')->user();
    if ($user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $submissions = StudentObjection::with(['user', 'objection'])
        ->whereHas('objection', function ($query) use ($subject_name) {
            $query->where('subject_name', $subject_name);
        })
        ->where('accepted', true)
        ->get();

    $filtered = $submissions->map(function ($submission) {
        return [
            'student_id' => optional($submission->user->student)->student_id ?? null,
            'name' => $submission->user->name ?? null,
            'original_grade' => $submission->grade,
            'new_grade' => $submission->new_grade,
            'lecturer_name' => $submission->lecturer_name,
            'test_hall' => $submission->test_hall,
            'subject_name' => optional($submission->objection)->subject_name,
            'subject_year' => optional($submission->objection)->subject_year,
            'subject_term' => optional($submission->objection)->subject_term,
            'submitted_at' => $submission->created_at->format('Y-m-d'),
        ];
    });

    return response()->json($filtered);
}

}
