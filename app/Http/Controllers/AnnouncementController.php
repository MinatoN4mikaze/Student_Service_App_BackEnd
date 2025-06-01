<?php

namespace App\Http\Controllers;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\User;
use App\Notifications\NewAnnouncementNotification;
class AnnouncementController extends Controller
{
    // عرض الإعلانات المتاحة حاليًا
    public function index()
    {
        // $now = Carbon::now();

        // $announcements = Announcement::where(function($query) use ($now) {
        //     $query->whereNull('start_at')->orWhere('start_at', '<=', $now);
        // })->where(function($query) use ($now) {
        //     $query->whereNull('end_at')->orWhere('end_at', '>=', $now);
        // })->get();
    
       $announcements = Announcement::with('user')->orderBy('created_at', 'desc')->get();


        return response()->json($announcements);
    }
    //تايع لرؤية الاعلان كاملا ان كان مؤلفا من عدد كبير منا لاسطر
public function show($id)
{

    $announcement = Announcement::with('user')->find($id);

    if (!$announcement) {
        return response()->json(['message' => 'الإعلان غير موجود'], 404);
    }

    return response()->json($announcement);
}


    // إنشاء إعلان جديد (admin فقط)
    public function store(Request $request)
    {
        $user = auth('sanctum')->user();

        if ($user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        
        ]);

         $announcement = Announcement::create([
        'title' => $request->title,
        'content' => $request->content,
        'user_id' => $user->id, // ربط الإعلان بالأدمن
    ]);
    //ارسال اشعار لكل المستخدمين
 $users = User::all();
    foreach ($users as $u) {
        $u->notify(new NewAnnouncementNotification($announcement));
    }
        return response()->json([
            'message' => 'تم انشاء الاعلان بنجاح',
            'announcement' => $announcement
        ], 201);
    }

    public function update(Request $request, $id)
{
    $user = auth('sanctum')->user();
   
    if ($user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }

    $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
    ]);
//التاكد من وجود اعلان
    $announcement = Announcement::find($id);
    if (!$announcement) {
        return response()->json(['message' => 'الإعلان غير موجود'], 404);
    }
//تحديث المعلومات
    $announcement->update([
        'title' => $request->title,
        'content' => $request->content,
    ]);

    return response()->json(['message' => 'تم تعديل الإعلان بنجاح', 'announcement' => $announcement]);
}
public function destroy($id)
{
    $user = auth('sanctum')->user();

    if ($user->role !== 'admin') {
        return response()->json(['message' => 'Unauthorized'], 403);
    }
// محاولة حذف اعلان غير موجود
    $announcement = Announcement::find($id);
    if (!$announcement) {
        return response()->json(['message' => 'الإعلان غير موجود'], 404);
    }

    $announcement->delete();

    return response()->json(['message' => 'تم حذف الإعلان بنجاح']);
}




}