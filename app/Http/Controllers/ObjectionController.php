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
        
    }public function submit(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'grade' => 'required|numeric|min:0|max:100',
            'lecturer_name' => 'required|min:1',
            'test_hall' => 'required',
            'subject_term' => 'required',
            'subject_year' => 'required',
            'subject_name' => 'required'
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
    
        // نجيب الاعتراض بالاسم ونتأكد من التواريخ
        $objection = Objection::where('subject_name', $request->subject_name)
            ->where('start_at', '<=', now())
            ->where('end_at', '>=', now())
            ->first();
    
        if (!$objection) {
            return response()->json(['message' => 'هذا الاعتراض غير متاح حاليًا'], 403);
        }
    
        // تحقق أن الطالب لم يقدم اعتراضًا من قبل على نفس المادة
        $alreadySubmitted = StudentObjection::where('user_id', $user->id)
        ->whereHas('objection', function ($query) use ($request) {
            $query->where('subject_name', $request->subject_name);
        })
        ->exists();
    
        if ($alreadySubmitted) {
            return response()->json(['message' => 'لقد قدمت هذا الاعتراض من قبل'], 409);
        }
    
        // حفظ الاعتراض
        $submission = StudentObjection::create([
            'user_id' => $user->id,
            'objection_id' => $objection->id, // نخزن id داخلياً
            "grade" => $request->grade,
            'lecturer_name' => $request->lecturer_name,
            'test_hall' => $request->test_hall,
            "subject_term" => $request->subject_term,
            "subject_year" => $request->subject_year,
        ]);
    
        return response()->json([
            'message' => 'تم تقديم الاعتراض بنجاح',
            'submission' => $submission,
            'subject_name' => $submission->subject_name,
            'student_name' => $submission->user->name,
            'student_number' => optional($submission->user->student)->student_id ?? 'غير متوفر'
        ], 201);
    }
    
    public function store(Request $request)
    {
        $user = auth('sanctum')->user();
        if ($user->role !== 'admin' ) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        $validator = Validator::make($request->all(), [
            'subject_name' => 'required|string|max:255',
            'subject_year' => 'required|string',
            'subject_term' => 'required|string',
            'start_at' => 'required|date',
            'end_at' => 'required|date',
        ], [
            'subject_name.required' => 'اسم المادة مطلوب',
            'subject_year.required' => 'سنة المادة مطلوبة',
            'subject_term.required' => 'فصل المادة مطلوب',
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
    
        // تحقق من وجود اعتراض سابق لنفس اسم المادة بنفس السنة والفصل
        $exists = Objection::where('subject_name', $validated['subject_name'])
            ->where('subject_year', $validated['subject_year'])
            ->where('subject_term', $validated['subject_term'])
            ->first();
    
        if ($exists) {
            return response()->json([
                'message' => 'تم تقديم اعتراض مسبق على هذه المادة لنفس السنة والفصل'
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
    
        // آخر اعتراض مضاف للمادة
        $objection = \App\Models\Objection::where('subject_name', $request->subject_name)
            ->latest('created_at')
            ->first();
    
        if (!$objection) {
            return response()->json(['message' => 'Subject not found'], 404);
        }
    
        $now = Carbon::now();
        $endDate = Carbon::parse($objection->end_at);
    
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
            'start_at' => $objection->start_at,
            'end_at' => $objection->end_at,
            'remaining' => $remainingText,
        ]);
    }
    
    
    public function subjectsByYearAndTerm(Request $request)
    {
        $request->validate([
            'year' => 'required|string',
            'term' => 'required|string',
        ]);
    
        $subjects = \App\Models\Objection::where('subject_year', $request->year)
            ->where('subject_term', $request->term)
            ->pluck('subject_name')
            ->unique()
            ->values();
    
        return response()->json(['subjects' => $subjects]);
    }
    public function allSubmissions(Request $request)
{
    $user = auth('sanctum')->user();
    if ($user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
    $request->validate([
        'subject_name' => 'required|string',
    ]);

    

    // جلب اسم المادة من الرابط ?subject_name=...
    $subjectName = $request->subject_name;

    if (!$subjectName) {
        return response()->json(['message' => 'اسم المادة مطلوب'], 400);
    }

    // جلب الاعتراضات المرتبطة بهذه المادة فقط عبر العلاقة مع جدول objections
    $submissions = StudentObjection::with(['user', 'objection'])
        ->whereHas('objection', function ($query) use ($subjectName) {
            $query->where('subject_name', $subjectName);
        })
        ->orderBy('created_at')
        ->get();

    // تصفية البيانات حسب الحقول المطلوبة فقط
    $filtered = $submissions->map(function ($submission) {
        return [
            'id' => $submission->id,
            'student_id' => optional($submission->user->student)->student_id ?? null,
            'name' => $submission->user->name ?? null,
            'grade' => $submission->grade,
            'created_at' => $submission->created_at->format('Y-m-d'),
            'lecturer_name' => $submission->lecturer_name,
            'test_hall' => $submission->test_hall,
            'subject_name' => optional($submission->objection)->subject_name,
            'subject_year' => optional($submission->objection)->subject_year,
            'subject_term' => optional($submission->objection)->subject_term,
        ];
    });

    return response()->json($filtered);
}


}    