<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendAnnouncementEmails;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AnnouncementController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_announcements', only: ['index', 'show']),
            new Middleware('permission:create_announcement', only: ['create', 'store']),
            new Middleware('permission:delete_announcement', only: ['destroy']),
            new Middleware('permission:send_announcement_emails', only: ['resend']),
        ];
    }

    public function index(Request $request)
    {
        $query = Announcement::with('creator')->latest();

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('target')) {
            $query->where('target', $request->target);
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }

        $announcements = $query->paginate(15);

        return view('admin.announcements.index', compact('announcements'));
    }

    public function create()
    {
        return view('admin.announcements.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'priority' => 'required|in:normal,important,urgent',
            'target' => 'required|in:all,local,foreign',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'send_email' => 'boolean',
        ], [
            'title.required' => 'عنوان التعميم مطلوب',
            'body.required' => 'نص التعميم مطلوب',
            'priority.required' => 'الأولوية مطلوبة',
            'target.required' => 'الفئة المستهدفة مطلوبة',
            'end_date.after_or_equal' => 'تاريخ الانتهاء يجب أن يكون بعد أو يساوي تاريخ البدء',
        ]);

        $announcement = Announcement::create([
            ...$validated,
            'send_email' => $request->boolean('send_email'),
            'created_by' => auth()->id(),
        ]);

        if ($announcement->send_email) {
            SendAnnouncementEmails::dispatch($announcement);
        }

        return redirect()->route('admin.announcements.index')
            ->with('success', 'تم إنشاء التعميم بنجاح' . ($announcement->send_email ? ' وجاري إرسال الإيميلات' : ''));
    }

    public function show(Announcement $announcement)
    {
        $announcement->load('creator');

        return view('admin.announcements.show', compact('announcement'));
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', 'تم حذف التعميم بنجاح');
    }

    public function resend(Announcement $announcement)
    {
        if ($announcement->is_sent && $announcement->sent_at && $announcement->sent_at->diffInMinutes(now()) < 5) {
            return redirect()->back()->with('error', 'تم إرسال هذا التعميم مؤخراً، يرجى الانتظار');
        }

        SendAnnouncementEmails::dispatch($announcement);

        return redirect()->back()->with('success', 'جاري إعادة إرسال التعميم عبر البريد الإلكتروني');
    }
}
