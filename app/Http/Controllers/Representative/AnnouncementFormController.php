<?php

namespace App\Http\Controllers\Representative;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\AnnouncementSubmission;
use App\Models\AnnouncementSubmissionFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AnnouncementFormController extends Controller
{
    public function show(Announcement $announcement)
    {
        if (!$announcement->isForm() || !$announcement->is_active) {
            return redirect()->route('representative.dashboard')
                ->with('error', __('announcements.form_not_active'));
        }

        $existingSubmission = AnnouncementSubmission::where('announcement_id', $announcement->id)
            ->where('representative_id', auth('representative')->id())
            ->first();

        if ($existingSubmission) {
            return redirect()->route('representative.dashboard')
                ->with('info', __('announcements.already_submitted'));
        }

        return view('representative.announcements.form', compact('announcement'));
    }

    public function store(Request $request, Announcement $announcement)
    {
        if (!$announcement->isForm() || !$announcement->is_active) {
            return redirect()->route('representative.dashboard')
                ->with('error', __('announcements.form_not_active'));
        }

        $representativeId = auth('representative')->id();

        $existing = AnnouncementSubmission::where('announcement_id', $announcement->id)
            ->where('representative_id', $representativeId)
            ->exists();

        if ($existing) {
            return redirect()->route('representative.dashboard')
                ->with('info', __('announcements.already_submitted'));
        }

        $fields = $announcement->form_fields ?? [];
        $rules = [];
        $data = [];

        foreach ($fields as $field) {
            $name = $field['name'];
            $fieldRules = [];

            $fieldRules[] = $field['required'] ? 'required' : 'nullable';

            switch ($field['type']) {
                case 'text':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:255';
                    break;
                case 'textarea':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:5000';
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'email':
                    $fieldRules[] = 'email';
                    break;
                case 'date':
                    $fieldRules[] = 'date';
                    break;
                case 'select':
                case 'radio':
                    if (!empty($field['options'])) {
                        $fieldRules[] = 'in:' . implode(',', $field['options']);
                    }
                    break;
                case 'checkbox':
                    $fieldRules[] = 'array';
                    break;
                case 'file':
                    $fieldRules[] = 'file';
                    $fieldRules[] = 'max:10240';
                    $fieldRules[] = 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx';
                    break;
            }

            $rules[$name] = implode('|', $fieldRules);

            if ($field['type'] === 'checkbox' && !empty($field['options'])) {
                $rules[$name . '.*'] = 'in:' . implode(',', $field['options']);
            }
        }

        $validated = $request->validate($rules);

        foreach ($fields as $field) {
            if ($field['type'] !== 'file') {
                $data[$field['name']] = $validated[$field['name']] ?? null;
            }
        }

        $submission = AnnouncementSubmission::create([
            'announcement_id' => $announcement->id,
            'representative_id' => $representativeId,
            'data' => $data,
            'submitted_at' => now(),
        ]);

        foreach ($fields as $field) {
            if ($field['type'] === 'file' && $request->hasFile($field['name'])) {
                $uploadedFile = $request->file($field['name']);
                $path = $uploadedFile->store('announcement_submissions/' . $submission->id, 'local');

                AnnouncementSubmissionFile::create([
                    'submission_id' => $submission->id,
                    'field_name' => $field['name'],
                    'file_path' => $path,
                    'original_name' => $uploadedFile->getClientOriginalName(),
                    'mime_type' => $uploadedFile->getMimeType(),
                ]);
            }
        }

        return redirect()->route('representative.dashboard')
            ->with('success', __('announcements.form_submitted_success'));
    }
}
