<?php
namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\NotificationEvent;
use App\Models\RoleNotificationSetting;
use Illuminate\Http\Request;

class RoleNotificationController extends Controller
{
    /**
     * Show role notifications list.
     */
    public function index($roleId)
    {
        $role = Role::findOrFail($roleId);
        $events = NotificationEvent::orderBy('module')->get();
        $settings = RoleNotificationSetting::where('role_id', $roleId)
            ->get()
            ->keyBy('notification_event_id');

        return view('notification-settings.role-notification', compact('role', 'events', 'settings'));
    }

    /**
     * Show Add Module form.
     */
    public function store(Request $request)
    {
        // dd($request->all());
        if ($request->has('settings')) {
            foreach ($request->settings as $item) {
                RoleNotificationSetting::updateOrCreate(
                    [
                        'role_id' => $request->role_id,
                        'notification_event_id' => $item['event_id']
                    ],
                    ['enabled' => $item['enabled']]
                );
            }

            return response()->json(['status' => 'success', 'message' => __('messages.updateSuccess')]);
        }

        // optional fallback if single update ever comes
        RoleNotificationSetting::updateOrCreate(
            [
                'role_id' => $request->role_id,
                'notification_event_id' => $request->notification_event_id
            ],
            ['enabled' => $request->enabled]
        );

        return response()->json(['status' => 'success', 'message' => __('messages.updateSuccess')]);
    }

}
