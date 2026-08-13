<?php

namespace App\Http\Controllers;

use App\Models\NotificationSetting;
use App\Support\CompanyContext;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request, CompanyContext $companyContext): View
    {
        $company = $companyContext->current($request->user());

        $setting = NotificationSetting::query()->firstOrCreate(
            [
                'company_id' => $company?->id,
                'user_id' => $request->user()->id,
            ],
            [
                'sounds_enabled' => true,
                'sound_volume' => 50,
                'channels' => ['database' => true, 'broadcast' => true],
            ],
        );

        return view('employee.notifications.index', [
            'notifications' => $request->user()->notifications()->latest()->paginate(20),
            'setting' => $setting,
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications->markAsRead();

        return back()->with('status', 'Notifications marked as read.');
    }

    public function updateSettings(Request $request, CompanyContext $companyContext): RedirectResponse
    {
        $company = $companyContext->current($request->user());

        $validated = $request->validate([
            'sounds_enabled' => ['nullable', 'boolean'],
            'sound_volume' => ['required', 'integer', 'min:0', 'max:100'],
        ]);

        NotificationSetting::query()->updateOrCreate(
            [
                'company_id' => $company?->id,
                'user_id' => $request->user()->id,
            ],
            [
                'sounds_enabled' => (bool) ($validated['sounds_enabled'] ?? false),
                'sound_volume' => $validated['sound_volume'],
                'channels' => ['database' => true, 'broadcast' => true],
            ],
        );

        return back()->with('status', 'Notification settings updated.');
    }
}
