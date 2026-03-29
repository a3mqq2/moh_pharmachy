<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendAnnouncementEmails;
use App\Models\Announcement;
use App\Models\AnnouncementSubmission;
use App\Models\AnnouncementSubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Storage;

class AnnouncementController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_announcements', only: ['index', 'show', 'submissions', 'showSubmission', 'downloadSubmissionFile']),
            new Middleware('permission:create_announcement', only: ['create', 'store']),
            new Middleware('permission:delete_announcement', only: ['destroy']),
            new Middleware('permission:send_announcement_emails', only: ['resend']),
        ];
    }

    public function index(Request $request)
    {
        $query = Announcement::with('creator')->withCount('submissions')->latest();

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        if ($request->filled('target')) {
            $query->where('target', $request->target);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
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
        $rules = [
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'required|in:message,form',
            'priority' => 'required|in:normal,important,urgent',
            'target' => 'required|in:all,local,foreign',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'send_email' => 'boolean',
        ];

        if ($request->input('type') === 'form') {
            $rules['form_fields'] = 'required|json';
        }

        $validated = $request->validate($rules, [
            'title.required' => __('announcements.validation_title_required'),
            'body.required' => __('announcements.validation_body_required'),
            'priority.required' => __('announcements.validation_priority_required'),
            'target.required' => __('announcements.validation_target_required'),
            'end_date.after_or_equal' => __('announcements.validation_end_date_after'),
            'form_fields.required' => __('announcements.form_fields_required'),
        ]);

        $data = [
            'title' => $validated['title'],
            'body' => $validated['body'],
            'type' => $validated['type'],
            'priority' => $validated['priority'],
            'target' => $validated['target'],
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'send_email' => $request->boolean('send_email'),
            'created_by' => auth()->id(),
        ];

        if ($validated['type'] === 'form' && isset($validated['form_fields'])) {
            $data['form_fields'] = json_decode($validated['form_fields'], true);
        }

        $announcement = Announcement::create($data);

        if ($announcement->send_email) {
            SendAnnouncementEmails::dispatchSync($announcement);
        }

        return redirect()->route('admin.announcements.index')
            ->with('success', $announcement->send_email ? __('announcements.created_success_email') : __('announcements.created_success'));
    }

    public function show(Announcement $announcement)
    {
        $announcement->load('creator');
        $announcement->loadCount('submissions');

        return view('admin.announcements.show', compact('announcement'));
    }

    public function destroy(Announcement $announcement)
    {
        $announcement->delete();

        return redirect()->route('admin.announcements.index')
            ->with('success', __('announcements.deleted_success'));
    }

    public function resend(Announcement $announcement)
    {
        if ($announcement->is_sent && $announcement->sent_at && $announcement->sent_at->diffInMinutes(now()) < 5) {
            return redirect()->back()->with('error', __('announcements.resend_too_soon'));
        }

        SendAnnouncementEmails::dispatchSync($announcement);

        return redirect()->back()->with('success', __('announcements.resend_queued'));
    }

    public function submissions(Announcement $announcement)
    {
        if (!$announcement->isForm()) {
            return redirect()->route('admin.announcements.show', $announcement);
        }

        $submissions = $announcement->submissions()
            ->with('representative')
            ->latest('submitted_at')
            ->paginate(20);

        return view('admin.announcements.submissions', compact('announcement', 'submissions'));
    }

    public function showSubmission(Announcement $announcement, AnnouncementSubmission $submission)
    {
        $submission->load(['representative', 'files']);

        return view('admin.announcements.submission-show', compact('announcement', 'submission'));
    }

    public function downloadSubmissionFile(AnnouncementSubmissionFile $file)
    {
        if (!Storage::disk('local')->exists($file->file_path)) {
            abort(404);
        }

        return Storage::disk('local')->download($file->file_path, $file->original_name);
    }
}
