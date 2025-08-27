<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminPollController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
{
    $user = Auth::user();
    
    $polls = Poll::with([
        'options' => function ($q) {
            $q->withCount('votes'); // يضيف عمود votes_count لكل خيار
        },
        'createdBy' // تحميل بيانات منشئ التصويت
    ])->get();

    return response()->json([
        'message' => 'تم جلب جميع التصويتات بنجاح',
        'polls' => $polls
    ]);
}

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = auth('sanctum')->user();
        if($user->role!='admin')
        {
            return response()->json(['message'=>' عذرا لا تملك الصلاحيات '],403);
        }
        $validated = $request->validate(
            [
                'question' => 'required|string|max:255',
                'options' => 'required|array|min:2',
                'options.*' => 'string|distinct|max:100',
                'duration_days' => 'required|integer|min:1|max:365',
            ]
        );
        $poll=Poll::create([
             'user_id' => $user->id,
             'question'=>$validated['question'],
             'ends_at'=>now()->addDays($validated['duration_days']),
        ]);
        foreach($validated['options'] as $option)
        {
            //اضافة الخيارات لجدول الخيارت
             $poll->options()->create([
            'option_text' => $option
            ]);
        }
        return response()->json([
        'message' => 'تم إنشاء التصويت بنجاح',
        'poll' => $poll->load('options')
         ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = auth('sanctum')->user();
        if($user->role!='admin')
        {
            return response()->json(['message'=>' عذرا لا تملك الصلاحيات '],403);
        }
        $poll=Poll::find($id);
        if(!$poll)
        {
            return response()->json(['message'=>'التصويت غير موجود'],404);
        }
          // تحميل الخيارات مع عدد الأصوات لكل خيار
        $poll->load(['options' => function ($q) {
             $q->withCount('votes');
         }]);
        return response()->json([
        'message' => 'تم جلب التصويت بنجاح',
        'poll' => $poll
         ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = auth('sanctum')->user();
        if($user->role!='admin')
        {
            return response()->json(['message'=>' عذرا لا تملك الصلاحيات '],403);
        }
        $poll=Poll::find($id);
        if(!$poll)
        {
            return response()->json(['message'=>'التصويت غير موجود'],404);
        }
        $poll->delete();
        return response()->json(['message'=>'تم حذف التصويت بنجاح'],200);

    }
}
