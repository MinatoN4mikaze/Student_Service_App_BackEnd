<?php

namespace App\Http\Controllers;

use App\Models\Objection;
use App\Models\StudentObjection;
use Illuminate\Http\Request;
use Carbon\Carbon;
class ObjectionController extends Controller
{
    public function index()
{
   
    $now = Carbon::now();

    //جلب كل الاعتراضات التي وقتها لم ينتهِ
    $objections = Objection::where('start_at', '<=', $now)
        ->where('end_at', '>=', $now)
        ->get();

    return response()->json($objections);
}

public function submit(Request $request,$objectionId)
{

    $request->validate([
        'grade' => 'required|numeric|min:0|max:100',
        'lecturer_name'=>"required|min:1",
        "test_hall"=>"required",
        "subject_term"=>"required",
        "subject_year"=>"required"
    ]);

    $user = auth('sanctum')->user();

    // تحقق أن الاعتراض لا يزال متاحًا
    $objection = Objection::where('id', $request->objection_id)
        ->where('start_at', '<=', Carbon::now())
        ->where('end_at', '>=', Carbon::now())
        ->first();

    if (!$objection) {
        return response()->json(['message' => 'هذا الاعتراض غير متاح حاليًا'], 400);
    }

    // تحقق أن الطالب لم يقدم اعتراضًا عليه من قبل
    $alreadySubmitted = StudentObjection::where('user_id', $user->id)
        ->where('objection_id', $request->objection_id)
        ->exists();

    if ($alreadySubmitted) {
        return response()->json(['message' => 'لقد قدمت هذا الاعتراض من قبل'], 409);
    }
 $submission->load(['objection', 'user.student']);
    // حفظ الاعتراض
    $submission = StudentObjection::create([
        'user_id' => $user->id,
        'objection_id' => $objectionId,
         'grade' => $request->grade,
        'lecturer_name' => $request->lecturer_name,
        'test_hall' => $request->test_hall,
        "subject_term"=>$request->subject_term,
          "subject_year"=>$request->subject_year,
    ]);

     $submission->load(['objection', 'user.student']);
     
     return response()->json([
        'message' => 'تم تقديم الاعتراض بنجاح',
        'submission' => $submission,
        'subject_name' => $submission->objection->subject_name,
        'student_name' => $submission->user->name,
        'student_number' => $submission->user->student->student_id ?? 'غير متوفر'
    ], 201);
}
public function allSubmissions()
{
    $user = auth('sanctum')->user();

    if ($user->role !== 'admin' && $user->role !== 'student affairs') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $submissions = StudentObjection::with(['user', 'objection'])
        ->orderBy('created_at')
        ->get();

    return response()->json($submissions);
}

public function store(Request $request)
{
    $user = auth('sanctum')->user();

    // تحقق من الصلاحية
    if ($user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    // التحقق من البيانات
    $request->validate([
        'subject_name' => 'required|string|max:255',
        'start_at' => 'required|date',
        'end_at' => 'required|date|after_or_equal:start_at',
    ]);

    // إنشاء الاعتراض
    $objection = Objection::create([
        'subject_name' => $request->subject_name,
        'start_at' => $request->start_at,
        'end_at' => $request->end_at,
    ]);

    return response()->json([
        'message' => 'Objection created successfully.',
        'objection' => $objection
    ], 201);
}

}
