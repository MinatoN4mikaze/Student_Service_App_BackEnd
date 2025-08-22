<?php

namespace App\Http\Controllers;

use App\Models\Objection;
use App\Models\StudentObjection;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class ObjectionController extends Controller
{ public function index(Request $request)
    {
        $request->validate([
            'subject_name' => 'required|string',
        ]);

        $objection = Objection::where('subject_name', $request->subject_name)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->first();

        if (!$objection) {
            return response()->json(['message' => 'لا يوجد اعتراض متاح لهذه المادة'], 404);
        }

        return response()->json($objection);
    }

    public function submit(Request $request, $objectionId)
    {
        $validator = Validator::make($request->all(), [
            'grade' => 'required|numeric|min:0|max:100',
            'lecturer_name' => 'required|min:1',
            'test_hall' => 'required',
            'subject_term' => 'required',
            'subject_year' => 'required',
            'subject_name' => 'required'
        ], [
            'grade.required' => 'الدرجة مطلوبة',
            'grade.max' => 'الدرجة يجب أن تكون ضمن المجال0-100',
            'grade.min' => 'الدرجة يجب أن تكون ضمن المجال0-100',
            'grade.numeric' => 'الدرجة يجب أن تكون عدد',
            'test_hall.required' => 'اسم القاعة مطلوب',
            'lecturer_name.required' => 'اسم المدرس مطلوب',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = auth('sanctum')->user();
        if ($user->role !== 'student') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // تحقق أن الطالب لم يقدم أكثر من اعتراضين
        $count = StudentObjection::where('user_id', $user->id)->count();
        if ($count >= 2) {
            return response()->json(['message' => 'لا يمكنك تقديم أكثر من اعتراضين.'], 403);
        }

        // تحقق أن الاعتراض موجود ومتاحة تواريخه
        $objection = Objection::where('id', $objectionId)
            ->where('start_at', '<=', Carbon::now())
            ->where('end_at', '>=', Carbon::now())
            ->first();

        if (!$objection) {
            return response()->json(['message' => 'هذا الاعتراض غير متاح حاليًا'], 403);
        }

        // تحقق أن الطالب لم يقدم اعتراضًا من قبل على نفس المادة
        $alreadySubmitted = StudentObjection::where('user_id', $user->id)
            ->where('subject_name', $request->subject_name)
            ->exists();

        if ($alreadySubmitted) {
            return response()->json(['message' => 'لقد قدمت هذا الاعتراض من قبل'], 409);
        }

        // حفظ الاعتراض
        $submission = StudentObjection::create([
            'user_id' => $user->id,
            'objection_id' => $objectionId,
            "grade" => $request->grade,
            'lecturer_name' => $request->lecturer_name,
            'test_hall' => $request->test_hall,
            "subject_term" => $request->subject_term,
            "subject_year" => $request->subject_year,
            "subject_name" => $request->subject_name
        ]);

        return response()->json([
            'message' => 'تم تقديم الاعتراض بنجاح',
            'submission' => $submission,
            'subject_name' => $submission->subject_name,
            'student_name' => $submission->user->name,
            'student_number' => optional($submission->user->student)->student_id ?? 'غير متوفر'
        ], 201);
    }

    public function allSubmissions(Request $request)
    {
        $user = auth('sanctum')->user();
        if ($user->role !== 'admin' && $user->role !== 'student affairs') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $subjectName = $request->query('subject_name');
        if (!$subjectName) {
            return response()->json(['message' => 'اسم المادة مطلوب'], 400);
        }

        $submissions = StudentObjection::with(['user', 'objection'])
            ->where('subject_name', $subjectName)
            ->orderBy('created_at')
            ->get();

        $filtered = $submissions->map(function ($submission) {
            return [
                'student_id' => optional($submission->user->student)->student_id ?? null,
                'name' => $submission->user->name ?? null,
                'grade' => $submission->grade,
                'created_at' => $submission->created_at->format('Y-m-d'),
                'lecturer_name' => $submission->lecturer_name,
                'test_hall' => $submission->test_hall,
            ];
        });

        return response()->json($filtered);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'subject_name' => 'required|string|max:255',
            'start_at' => 'required|date',
            'end_at' => 'required|date',
        ], [
            'subject_name.required' => 'اسم المادة مطلوب',
            'start_at.required' => 'تاريخ البدء مطلوب',
            'start_at.date' => 'تاريخ البدء يجب أن يكون تاريخًا صالحًا',
            'end_at.required' => 'تاريخ الانتهاء مطلوب',
            'end_at.date' => 'تاريخ الانتهاء يجب أن يكون تاريخًا صالحًا',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $validated = $validator->validated();

        if (strtotime($validated['end_at']) < strtotime($validated['start_at'])) {
            return response()->json([
                'message' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البدء'
            ], 422);
        }

        // تحقق من وجود اعتراض سابق لنفس اسم المادة
        $exists = Objection::where('subject_name', $validated['subject_name'])->first();
        if ($exists) {
            return response()->json([
                'message' => 'تم تقديم اعتراض مسبق على هذه المادة'
            ], 409);
        }

        $objection = Objection::create($validated);

        return response()->json([
            'message' => 'تم إنشاء الاعتراض',
            'data' => $objection
        ], 201);
    }

public function datesForSubject(Request $request)
{
    $request->validate([
        'subject_name' => 'required|string',
    ]);

    $objection = \App\Models\StudentObjection::where('subject_name', $request->subject_name)
        ->latest('created_at') // أحدث اعتراض للطالب على المادة
        ->first();

    if (!$objection) {
        return response()->json(['message' => 'Subject not found'], 404);
    }

    $now = Carbon::now();
    $endDate = Carbon::parse($objection->objection->end_at ?? now());

    $remainingDays = $now->diffInDays($endDate, false);

    if ($remainingDays <= 0) {
        $remainingText = "Objection period expired";
    } else {
        $weeks = intdiv($remainingDays, 7);
        $days = $remainingDays % 7;

        $parts = [];
        if ($weeks > 0) {
            $parts[] = "$weeks " . ($weeks === 1 ? 'week' : 'weeks');
        }
        if ($days > 0) {
            $parts[] = "$days " . ($days === 1 ? 'day' : 'days');
        }

        $remainingText = implode(' and ', $parts);
    }

    return response()->json([
        'start_at' => $objection->objection->start_at ?? null,
        'end_at' => $objection->objection->end_at ?? null,
        'remaining' => $remainingText,
    ]);
}


public function subjectsByYearAndTerm(Request $request)
{
    $request->validate([
        'year' => 'required|string',
        'term' => 'required|string',
    ]);

    $subjects = \App\Models\StudentObjection::where('subject_year', $request->year)
        ->where('subject_term', $request->term)
        ->pluck('subject_name')
        ->unique()
        ->values();

    return response()->json(['subjects' => $subjects]);
}



}