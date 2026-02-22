<?php

namespace App\Customer\Controllers\Web;

use App\Customer\Concerns\ResolvesCustomerFromUser;
use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    use ResolvesCustomerFromUser;

    public function notifications(Request $request): View
    {
        $this->authorizeCustomerPermission('customer.portal.notifications.view');
        $user = Auth::user();

        $query = Notification::where('user_id', $user->id)
            ->where('channel', 'dashboard');

        if ($request->filled('is_read')) {
            $query->where('is_read', $request->is_read === '1');
        }

        if ($request->filled('notification_type')) {
            $query->where('notification_type', $request->notification_type);
        }

        $notifications = $query->latest('created_at')->paginate(20);
        $unreadCount = $user->unreadNotificationsCount();

        return view('customer.notifications.index', compact('notifications', 'unreadCount'));
    }

    public function showNotification(Notification $notification): View
    {
        $this->authorizeCustomerPermission('customer.portal.notifications.view');
        $user = Auth::user();

        if ($notification->user_id !== $user->id) {
            abort(403, 'Bu bildirime erişim yetkiniz yok.');
        }

        if (! $notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return view('customer.notifications.show', compact('notification'));
    }

    public function markNotificationAsRead(Notification $notification): RedirectResponse
    {
        $this->authorizeCustomerPermission('customer.portal.notifications.view');
        $user = Auth::user();

        if ($notification->user_id !== $user->id) {
            abort(403, 'Bu bildirime erişim yetkiniz yok.');
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()->with('success', 'Bildirim okundu olarak işaretlendi.');
    }

    public function markAllNotificationsAsRead(): RedirectResponse
    {
        $this->authorizeCustomerPermission('customer.portal.notifications.view');
        $user = Auth::user();

        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('success', 'Tüm bildirimler okundu olarak işaretlendi.');
    }
}
