<?php

namespace App\Http\Controllers;

use App\Models\Poll;
use App\Models\PollOption;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Js;

class PollController extends Controller
{
     public function index()
     {
        $user=Auth::user();
        $polls = Poll::with('options')
                ->where('ends_at', '>', now())
                ->get();
         return response()->json([
           'polls' => $polls
        ]);

     }
    public function show(Poll $poll)
    {
         $user=Auth::user();
        if($user->role!='student')
        {
            return response()->json(['message'=>' عذرا لا تملك الصلاحيات '],403);
        }

        if ($poll->ends_at <= now())
        {
             return response()->json(['message' => 'انتهى وقت التصويت'], 403);
        }

         return response()->json([
           'poll' => $poll->load('options')
        ]);

    }

   /* public function vote(Request $request, Poll $poll)
    {
        $user = Auth::user();

        // تحقق صلاحية الطالب
        if ($user->role !== 'student') {
            return response()->json(['message' => 'عذراً، لا تملك الصلاحيات'], 403);
        }

        // تحقق أن التصويت ما انتهى
        if ($poll->ends_at <= now()) {
            return response()->json(['message' => 'انتهى وقت التصويت ولا يمكنك التغيير'], 403);
        }

        // تحقق من صحة الطلب
        $validated = $request->validate([
            'option_id' => 'required|integer|exists:poll_options,id',
        ]);
        // تأكد أن الخيار تابع لهذا التصويت
        $option = PollOption::where('id', $validated['option_id'])
        ->where('poll_id', $poll->id)
        ->first();

        if (!$option) {
            return response()->json(['message' => 'الخيار غير تابع لهذا التصويت'], 422);
        }

        // جمع ids كل الخيارات التابعة لهذا poll
        $optionIds = $poll->options->pluck('id')->toArray();

        // نستخدم transaction لضمان الاتساق
        DB::transaction(function () use ($user, $optionIds, $validated) {
            // نحذف أي تصويت سابق لهذا المستخدم ضمن هذا التصويت
            Vote::where('user_id', $user->id)
            ->whereIn('poll_option_id', $optionIds)
            ->delete();

            // نسجّل التصويت الجديد
            Vote::create([
                'user_id' => $user->id,
                'poll_option_id' => $validated['option_id'],
            ]);
        });
        return response()->json(['message' => 'تم تسجيل التصويت بنجاح']);
    }*/
    public function vote(Request $request, Poll $poll)
{
    $user = Auth::user();

    // التحقق من الدور
    if ($user->role !== 'student') {
        return response()->json([
            'message' => 'عذرًا، لا تملك الصلاحيات',
            'status'  => 'forbidden'
        ], 403);
    }

    // التحقق من انتهاء وقت التصويت
    if ($poll->ends_at <=now()) {
        return response()->json([
            'message' => 'انتهى وقت التصويت',
            'status'  => 'poll_expired'
        ], 403);
    }

    // التحقق من صحة الإدخال
    $validated = $request->validate([
        'option_id' => 'required|exists:poll_options,id'
    ]);

    // التحقق إن الخيار مرتبط فعلاً بالتصويت
    $option = PollOption::where('id', $validated['option_id'])
        ->where('poll_id', $poll->id)
        ->first();

    if (!$option) {
        return response()->json([
            'message' => 'الخيار غير مرتبط بهذا التصويت',
            'status'  => 'invalid_option'
        ], 400);
    }

    // التحقق إذا كان الطالب صوّت من قبل
    $alreadyVoted = Vote::where('user_id', $user->id)
        ->whereIn('poll_option_id', $poll->options->pluck('id'))
        ->exists();

    if ($alreadyVoted) {
        return response()->json([
            'message' => 'لقد قمت بالتصويت مسبقًا',
            'status'  => 'already_voted'
        ], 409);
    }

    // إنشاء التصويت
    Vote::create([
        'user_id'        => $user->id,
        'poll_id'        => $poll->id,
        'poll_option_id' => $validated['option_id'],
    ]);

    return response()->json([
        'message' => 'تم التصويت بنجاح',
        'status'  => 'vote_success'
    ], 200);
}


public function deleteVote(Poll $poll)
{
    $user = Auth::user();

    // التحقق من الدور
    if ($user->role !== 'student') {
        return response()->json([
            'message' => 'عذراً، لا تملك الصلاحيات',
            'status'  => 'forbidden'
        ], 403);
    }

    // التحقق من انتهاء وقت التصويت (اختياري)
    if ($poll->ends_at <= now()) {
        return response()->json([
            'message' => 'انتهى وقت التصويت، لا يمكن حذف التصويت',
            'status'  => 'poll_expired'
        ], 403);
    }

    // الحصول على خيارات التصويت
    $optionIds = $poll->options->pluck('id')->toArray();

    $deleted = Vote::where('user_id', $user->id)
        ->whereIn('poll_option_id', $optionIds)
        ->delete();

    if ($deleted) {
        return response()->json([
            'message' => 'تم حذف التصويت بنجاح',
            'status'  => 'vote_deleted'
        ], 200);
    }

    return response()->json([
        'message' => 'لم يتم العثور على تصويت لحذفه',
        'status'  => 'no_vote_found'
    ], 404);
}

     public function results($id)
     {
        $user = Auth::user();
        $poll=Poll::find($id);
        $poll->load(['options' => fn($q) => $q->withCount('votes')]);

        return response()->json([
        'poll_id' => $poll->id,
        'question' => $poll->question,
        'results' => $poll->options->map(function ($option) {
            return [
                'option' => $option->option_text,
                'votes' => $option->votes_count
            ];
        })
    ]);

    }

}
