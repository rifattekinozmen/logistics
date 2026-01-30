<?php

namespace App\Notification\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Bildirim listesi (yönetim paneli).
     */
    public function index(Request $request): View
    {
        $user = Auth::user();
        $company = $user->activeCompany();

        if (!$company) {
            abort(403, 'Aktif bir firma seçmeden bildirimleri görüntüleyemezsiniz.');
        }

        $query = Notification::query();

        // Filtreler
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('notification_type')) {
            $query->where('notification_type', $request->notification_type);
        }

        if ($request->filled('is_read')) {
            $query->where('is_read', $request->is_read === '1');
        }

        $notifications = $query->latest('created_at')->paginate(30);

        // İstatistikler
        $stats = [
            'total' => Notification::count(),
            'pending' => Notification::where('status', 'pending')->count(),
            'sent' => Notification::where('status', 'sent')->count(),
            'failed' => Notification::where('status', 'failed')->count(),
            'unread' => Notification::where('is_read', false)->count(),
        ];

        return view('admin.notifications.index', [
            'notifications' => $notifications,
            'stats' => $stats,
            'filters' => $request->only(['status', 'channel', 'notification_type', 'is_read']),
        ]);
    }

    /**
     * Bildirim detayı.
     */
    public function show(Notification $notification): View
    {
        // Okundu işaretle
        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return view('admin.notifications.show', compact('notification'));
    }

    /**
     * Bildirimleri toplu okundu işaretle.
     */
    public function markAllAsRead(): RedirectResponse
    {
        Notification::where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('success', 'Tüm bildirimler okundu olarak işaretlendi.');
    }
}
