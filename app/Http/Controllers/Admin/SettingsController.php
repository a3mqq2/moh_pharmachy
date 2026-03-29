<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $foreignCompanySettings = Setting::where('group', 'foreign_companies')->get();
        $localCompanySettings = Setting::where('group', 'local_companies')->get();

        return view('admin.settings.index', compact('foreignCompanySettings', 'localCompanySettings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*' => 'required|numeric|min:0',
        ]);

        foreach ($validated['settings'] as $key => $value) {
            Setting::set($key, $value);
        }

        Setting::clearCache();

        return redirect()->route('admin.app-settings.index')
            ->with('success', __('settings.settings_updated'));
    }

    public function notifications()
    {
        $user = auth()->user();

        $notifications = $user->notifications()->latest()->paginate(20);

        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $user = auth()->user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->back()->with('success', __('settings.notification_marked_read'));
    }

    public function markAllAsRead()
    {
        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->back()->with('success', __('settings.all_notifications_marked_read'));
    }

    public function destroy($id)
    {
        $user = auth()->user();
        $notification = $user->notifications()->findOrFail($id);
        $notification->delete();

        return redirect()->back()->with('success', __('settings.notification_deleted'));
    }

    public function deleteAll()
    {
        auth()->user()->notifications()->delete();

        return redirect()->back()->with('success', __('settings.all_notifications_deleted'));
    }
}
