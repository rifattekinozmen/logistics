<?php

namespace App\Customer\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Document;
use App\Models\Customer;
use App\Models\Payment;
use App\Models\FavoriteAddress;
use App\Models\OrderTemplate;
use App\Order\Services\OrderService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerPortalController extends Controller
{
    public function __construct(
        protected OrderService $orderService
    ) {}

    /**
     * Müşteri dashboard.
     */
    public function dashboard(): View
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.dashboard')) {
            abort(403, 'Bu sayfaya erişim yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer) {
            abort(404, 'Müşteri kaydı bulunamadı.');
        }

        // Aktif siparişler
        $activeOrders = Order::where('customer_id', $customer->id)
            ->whereNotIn('status', ['delivered', 'cancelled'])
            ->latest()
            ->limit(5)
            ->get();

        // Son teslim edilenler
        $recentDelivered = Order::where('customer_id', $customer->id)
            ->where('status', 'delivered')
            ->latest('delivered_at')
            ->limit(5)
            ->get();

        // İstatistikler
        $stats = [
            'total_orders' => Order::where('customer_id', $customer->id)->count(),
            'active_orders' => Order::where('customer_id', $customer->id)
                ->whereNotIn('status', ['delivered', 'cancelled'])
                ->count(),
            'delivered_orders' => Order::where('customer_id', $customer->id)
                ->where('status', 'delivered')
                ->count(),
            'pending_payments' => Payment::where('related_type', Customer::class)
                ->where('related_id', $customer->id)
                ->where('status', 0)
                ->sum('amount'),
            'overdue_payments_count' => Payment::where('related_type', Customer::class)
                ->where('related_id', $customer->id)
                ->where('status', 0)
                ->whereDate('due_date', '<', now())
                ->count(),
        ];

        // Aylık sipariş trendi (son 6 ay)
        $monthlyOrders = Order::where('customer_id', $customer->id)
            ->where('created_at', '>=', now()->subMonths(6))
            ->selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->groupByRaw('YEAR(created_at), MONTH(created_at)')
            ->orderByRaw('YEAR(created_at) ASC, MONTH(created_at) ASC')
            ->get()
            ->map(function ($item) {
                return [
                    'label' => \Carbon\Carbon::create($item->year, $item->month, 1)->format('M Y'),
                    'count' => $item->count,
                ];
            });

        // Durum bazlı sipariş dağılımı
        $statusDistribution = Order::where('customer_id', $customer->id)
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        return view('customer.dashboard', compact('customer', 'activeOrders', 'recentDelivered', 'stats', 'monthlyOrders', 'statusDistribution'));
    }

    /**
     * Sipariş listesi.
     */
    public function orders(Request $request): View
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.orders.view')) {
            abort(403, 'Siparişleri görüntüleme yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer) {
            abort(404, 'Müşteri kaydı bulunamadı.');
        }

        $query = Order::where('customer_id', $customer->id);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Export kontrolü
        if ($request->has('export') && $request->export === 'csv') {
            return $this->exportOrdersToCsv($query->get());
        }

        $orders = $query->latest()->paginate(20);

        return view('customer.orders.index', compact('orders'));
    }

    /**
     * Siparişleri CSV olarak export et.
     */
    protected function exportOrdersToCsv($orders): StreamedResponse
    {
        $filename = 'siparisler_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
        ];

        $callback = function () use ($orders) {
            $file = fopen('php://output', 'w');
            
            // BOM ekle (UTF-8 için)
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Başlık satırı
            fputcsv($file, [
                'Sipariş No',
                'Durum',
                'Alış Adresi',
                'Teslimat Adresi',
                'Planlanan Teslimat',
                'Teslim Tarihi',
                'Ağırlık (kg)',
                'Hacim (m³)',
                'Oluşturulma Tarihi',
            ], ';');

            // Veri satırları
            foreach ($orders as $order) {
                fputcsv($file, [
                    $order->order_number,
                    $order->status,
                    $order->pickup_address,
                    $order->delivery_address,
                    $order->planned_delivery_date?->format('d.m.Y H:i') ?? '-',
                    $order->delivered_at?->format('d.m.Y H:i') ?? '-',
                    $order->total_weight ?? '-',
                    $order->total_volume ?? '-',
                    $order->created_at->format('d.m.Y H:i'),
                ], ';');
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Yeni sipariş formu.
     */
    public function createOrder(): View
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.orders.create')) {
            abort(403, 'Sipariş oluşturma yetkiniz bulunmamaktadır.');
        }

        return view('customer.orders.create');
    }

    /**
     * Sipariş oluştur.
     */
    public function storeOrder(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.orders.create')) {
            abort(403, 'Sipariş oluşturma yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer) {
            return back()->withErrors(['customer' => 'Müşteri kaydı bulunamadı.']);
        }

        $validated = $request->validate([
            'pickup_address' => 'required|string|max:1000',
            'delivery_address' => 'required|string|max:1000',
            'planned_delivery_date' => 'required|date|after:today',
            'total_weight' => 'nullable|numeric|min:0',
            'total_volume' => 'nullable|numeric|min:0',
            'is_dangerous' => 'boolean',
            'notes' => 'nullable|string|max:2000',
        ]);

        $order = $this->orderService->create($validated, $user);
        $order->update(['customer_id' => $customer->id]);

        return redirect()
            ->route('customer.orders.show', $order)
            ->with('success', 'Siparişiniz başarıyla oluşturuldu.');
    }

    /**
     * Sipariş detayı.
     */
    public function showOrder(Order $order): View
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.orders.view')) {
            abort(403, 'Siparişleri görüntüleme yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        // Müşteri kontrolü
        if (!$customer || $order->customer_id !== $customer->id) {
            abort(403, 'Bu siparişe erişim yetkiniz yok.');
        }

        $order->load(['customer', 'shipments.vehicle', 'shipments.driver']);

        return view('customer.orders.show', compact('order'));
    }

    /**
     * Belge listesi.
     */
    public function documents(Request $request): View
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.documents.view')) {
            abort(403, 'Belgeleri görüntüleme yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer) {
            abort(404, 'Müşteri kaydı bulunamadı.');
        }

        // Müşteriye ait siparişlerin belgeleri
        $orderIds = Order::where('customer_id', $customer->id)->pluck('id');
        
        $query = Document::where('documentable_type', Order::class)
            ->whereIn('documentable_id', $orderIds);

        // Filtreleme
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($request->filled('order_id')) {
            $query->where('documentable_id', $request->order_id);
        }

        // Toplu indirme kontrolü
        if ($request->has('download_selected') && $request->filled('selected_documents')) {
            return $this->downloadMultipleDocuments($request->selected_documents, $customer);
        }

        $documents = $query->latest()->paginate(20);

        // Kategoriler listesi
        $categories = Document::where('documentable_type', Order::class)
            ->whereIn('documentable_id', $orderIds)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        // Sipariş listesi (filtreleme için)
        $orders = Order::where('customer_id', $customer->id)
            ->orderBy('order_number')
            ->get(['id', 'order_number']);

        return view('customer.documents.index', compact('documents', 'categories', 'orders'));
    }

    /**
     * Toplu belge indirme.
     */
    protected function downloadMultipleDocuments(array $documentIds, Customer $customer): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.documents.download')) {
            abort(403, 'Belge indirme yetkiniz bulunmamaktadır.');
        }

        $orderIds = Order::where('customer_id', $customer->id)->pluck('id');
        
        $documents = Document::where('documentable_type', Order::class)
            ->whereIn('documentable_id', $orderIds)
            ->whereIn('id', $documentIds)
            ->get();

        if ($documents->isEmpty()) {
            return back()->withErrors(['documents' => 'İndirilecek belge bulunamadı.']);
        }

        // ZIP dosyası oluştur
        $zipFileName = 'belgeler_' . now()->format('Y-m-d_His') . '.zip';
        $zipPath = storage_path('app/temp/' . $zipFileName);

        // Temp dizini yoksa oluştur
        if (!file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE) !== true) {
            return back()->withErrors(['documents' => 'ZIP dosyası oluşturulamadı.']);
        }

        foreach ($documents as $document) {
            $filePath = Storage::disk('public')->path($document->file_path);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, $document->name);
            }
        }

        $zip->close();

        return response()->download($zipPath, $zipFileName)->deleteFileAfterSend(true);
    }

    /**
     * Belge indir.
     */
    public function downloadDocument(Document $document): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.documents.download')) {
            abort(403, 'Belge indirme yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer) {
            abort(404, 'Müşteri kaydı bulunamadı.');
        }

        // Belge müşteriye ait mi kontrol et
        if ($document->documentable_type === Order::class) {
            $order = Order::find($document->documentable_id);
            if (!$order || $order->customer_id !== $customer->id) {
                abort(403, 'Bu belgeye erişim yetkiniz yok.');
            }
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->withErrors(['document' => 'Belge dosyası bulunamadı.']);
        }

        return Storage::disk('public')->download($document->file_path, $document->name);
    }

    /**
     * Profil sayfası.
     */
    public function profile(): View
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.profile.view')) {
            abort(403, 'Profil görüntüleme yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer) {
            abort(404, 'Müşteri kaydı bulunamadı.');
        }

        return view('customer.profile', compact('customer'));
    }

    /**
     * Profil güncelle.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.profile.update')) {
            abort(403, 'Profil güncelleme yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer) {
            return back()->withErrors(['customer' => 'Müşteri kaydı bulunamadı.']);
        }

        $validated = $request->validate([
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:1000',
        ]);

        $customer->update($validated);

        return back()->with('success', 'Profil başarıyla güncellendi.');
    }

    /**
     * Şifre değiştir.
     */
    public function changePassword(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.profile.update')) {
            abort(403, 'Profil güncelleme yetkiniz bulunmamaktadır.');
        }

        $validated = $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => 'Mevcut şifre gereklidir.',
            'password.required' => 'Yeni şifre gereklidir.',
            'password.min' => 'Yeni şifre en az 8 karakter olmalıdır.',
            'password.confirmed' => 'Yeni şifreler eşleşmiyor.',
        ]);

        // Mevcut şifre kontrolü
        if (!\Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Mevcut şifre yanlış.']);
        }

        // Yeni şifreyi güncelle
        $user->update([
            'password' => \Hash::make($validated['password']),
        ]);

        return back()->with('success', 'Şifre başarıyla değiştirildi.');
    }

    /**
     * Ödeme listesi.
     */
    public function payments(Request $request): View
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.payments.view')) {
            abort(403, 'Ödemeleri görüntüleme yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer) {
            abort(404, 'Müşteri kaydı bulunamadı.');
        }

        $query = Payment::where('related_type', Customer::class)
            ->where('related_id', $customer->id);

        // Filtreleme
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('payment_type')) {
            $query->where('payment_type', $request->payment_type);
        }

        if ($request->filled('due_date_from')) {
            $query->whereDate('due_date', '>=', $request->due_date_from);
        }

        if ($request->filled('due_date_to')) {
            $query->whereDate('due_date', '<=', $request->due_date_to);
        }

        $payments = $query->latest('due_date')->paginate(20);

        // İstatistikler
        $stats = [
            'total_pending' => Payment::where('related_type', Customer::class)
                ->where('related_id', $customer->id)
                ->where('status', 0)
                ->sum('amount'),
            'total_paid' => Payment::where('related_type', Customer::class)
                ->where('related_id', $customer->id)
                ->where('status', 1)
                ->sum('amount'),
            'overdue_count' => Payment::where('related_type', Customer::class)
                ->where('related_id', $customer->id)
                ->where('status', 0)
                ->whereDate('due_date', '<', now())
                ->count(),
        ];

        return view('customer.payments.index', compact('payments', 'stats'));
    }

    /**
     * Ödeme detayı.
     */
    public function showPayment(Payment $payment): View
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.payments.view')) {
            abort(403, 'Ödemeleri görüntüleme yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer) {
            abort(404, 'Müşteri kaydı bulunamadı.');
        }

        // Ödeme müşteriye ait mi kontrol et
        if ($payment->related_type !== Customer::class || $payment->related_id !== $customer->id) {
            abort(403, 'Bu ödemeye erişim yetkiniz yok.');
        }

        return view('customer.payments.show', compact('payment'));
    }

    /**
     * Fatura listesi.
     */
    public function invoices(Request $request): View
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.invoices.view')) {
            abort(403, 'Faturaları görüntüleme yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer) {
            abort(404, 'Müşteri kaydı bulunamadı.');
        }

        // Müşteriye ait siparişlerin belgeleri (fatura kategorisi)
        $orderIds = Order::where('customer_id', $customer->id)->pluck('id');
        
        $query = Document::where('documentable_type', Order::class)
            ->whereIn('documentable_id', $orderIds)
            ->where('category', 'invoice');

        if ($request->filled('order_id')) {
            $query->where('documentable_id', $request->order_id);
        }

        $invoices = $query->latest()->paginate(20);

        return view('customer.invoices.index', compact('invoices'));
    }

    /**
     * Fatura indir.
     */
    public function downloadInvoice(Document $document): \Symfony\Component\HttpFoundation\StreamedResponse|RedirectResponse
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.invoices.download')) {
            abort(403, 'Fatura indirme yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer) {
            abort(404, 'Müşteri kaydı bulunamadı.');
        }

        // Belge müşteriye ait mi kontrol et
        if ($document->documentable_type === Order::class) {
            $order = Order::find($document->documentable_id);
            if (!$order || $order->customer_id !== $customer->id || $document->category !== 'invoice') {
                abort(403, 'Bu faturaya erişim yetkiniz yok.');
            }
        }

        if (!Storage::disk('public')->exists($document->file_path)) {
            return back()->withErrors(['document' => 'Fatura dosyası bulunamadı.']);
        }

        return Storage::disk('public')->download($document->file_path, $document->name);
    }

    /**
     * Bildirim listesi.
     */
    public function notifications(Request $request): View
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.notifications.view')) {
            abort(403, 'Bildirimleri görüntüleme yetkiniz bulunmamaktadır.');
        }

        $query = \App\Models\Notification::where('user_id', $user->id)
            ->where('channel', 'dashboard');

        // Filtreleme
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

    /**
     * Bildirim detayı.
     */
    public function showNotification(\App\Models\Notification $notification): View
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.notifications.view')) {
            abort(403, 'Bildirimleri görüntüleme yetkiniz bulunmamaktadır.');
        }

        // Bildirim kullanıcıya ait mi kontrol et
        if ($notification->user_id !== $user->id) {
            abort(403, 'Bu bildirime erişim yetkiniz yok.');
        }

        // Okundu işaretle
        if (!$notification->is_read) {
            $notification->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
        }

        return view('customer.notifications.show', compact('notification'));
    }

    /**
     * Bildirimi okundu işaretle.
     */
    public function markNotificationAsRead(\App\Models\Notification $notification): RedirectResponse
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.notifications.view')) {
            abort(403, 'Bildirimleri görüntüleme yetkiniz bulunmamaktadır.');
        }

        // Bildirim kullanıcıya ait mi kontrol et
        if ($notification->user_id !== $user->id) {
            abort(403, 'Bu bildirime erişim yetkiniz yok.');
        }

        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);

        return back()->with('success', 'Bildirim okundu olarak işaretlendi.');
    }

    /**
     * Tüm bildirimleri okundu işaretle.
     */
    public function markAllNotificationsAsRead(): RedirectResponse
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.notifications.view')) {
            abort(403, 'Bildirimleri görüntüleme yetkiniz bulunmamaktadır.');
        }

        \App\Models\Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return back()->with('success', 'Tüm bildirimler okundu olarak işaretlendi.');
    }

    /**
     * Sipariş iptal et.
     */
    public function cancelOrder(Request $request, Order $order): RedirectResponse
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.orders.cancel')) {
            abort(403, 'Sipariş iptal etme yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer) {
            return back()->withErrors(['customer' => 'Müşteri kaydı bulunamadı.']);
        }

        // Sipariş müşteriye ait mi kontrol et
        if ($order->customer_id !== $customer->id) {
            abort(403, 'Bu siparişe erişim yetkiniz yok.');
        }

        // Sadece belirli durumlarda iptal edilebilir
        if (!in_array($order->status, ['pending', 'assigned'])) {
            return back()->withErrors(['order' => 'Bu sipariş iptal edilemez. Sadece beklemede veya atanmış siparişler iptal edilebilir.']);
        }

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:1000',
        ]);

        $order->update([
            'status' => 'cancelled',
            'notes' => ($order->notes ? $order->notes . "\n\n" : '') . 'İptal Nedeni: ' . ($validated['cancellation_reason'] ?? 'Müşteri talebi'),
        ]);

        return redirect()
            ->route('customer.orders.show', $order)
            ->with('success', 'Sipariş başarıyla iptal edildi.');
    }

    /**
     * Favori adresler listesi.
     */
    public function favoriteAddresses(): View
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.favorite-addresses.manage')) {
            abort(403, 'Favori adresler yönetme yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer) {
            abort(404, 'Müşteri kaydı bulunamadı.');
        }

        $addresses = FavoriteAddress::where('customer_id', $customer->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('customer.favorite-addresses.index', compact('addresses'));
    }

    /**
     * Favori adres oluştur.
     */
    public function storeFavoriteAddress(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.favorite-addresses.manage')) {
            abort(403, 'Favori adresler yönetme yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer) {
            return back()->withErrors(['customer' => 'Müşteri kaydı bulunamadı.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:pickup,delivery,both',
            'address' => 'required|string|max:1000',
            'contact_name' => 'nullable|string|max:255',
            'contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
        ]);

        FavoriteAddress::create(array_merge($validated, ['customer_id' => $customer->id]));

        return back()->with('success', 'Favori adres başarıyla eklendi.');
    }

    /**
     * Favori adres sil.
     */
    public function deleteFavoriteAddress(FavoriteAddress $favoriteAddress): RedirectResponse
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.favorite-addresses.manage')) {
            abort(403, 'Favori adresler yönetme yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer || $favoriteAddress->customer_id !== $customer->id) {
            abort(403, 'Bu adrese erişim yetkiniz yok.');
        }

        $favoriteAddress->delete();

        return back()->with('success', 'Favori adres başarıyla silindi.');
    }

    /**
     * Sipariş şablonları listesi.
     */
    public function orderTemplates(): View
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.order-templates.manage')) {
            abort(403, 'Sipariş şablonları yönetme yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer) {
            abort(404, 'Müşteri kaydı bulunamadı.');
        }

        $templates = OrderTemplate::where('customer_id', $customer->id)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('customer.order-templates.index', compact('templates'));
    }

    /**
     * Sipariş şablonu oluştur.
     */
    public function storeOrderTemplate(Request $request): RedirectResponse
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.order-templates.manage')) {
            abort(403, 'Sipariş şablonları yönetme yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer) {
            return back()->withErrors(['customer' => 'Müşteri kaydı bulunamadı.']);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'pickup_address' => 'required|string|max:1000',
            'delivery_address' => 'required|string|max:1000',
            'total_weight' => 'nullable|numeric|min:0',
            'total_volume' => 'nullable|numeric|min:0',
            'is_dangerous' => 'boolean',
            'notes' => 'nullable|string|max:2000',
        ]);

        OrderTemplate::create(array_merge($validated, ['customer_id' => $customer->id]));

        return back()->with('success', 'Sipariş şablonu başarıyla eklendi.');
    }

    /**
     * Sipariş şablonundan sipariş oluştur.
     */
    public function createOrderFromTemplate(OrderTemplate $orderTemplate): RedirectResponse
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.orders.create')) {
            abort(403, 'Sipariş oluşturma yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer || $orderTemplate->customer_id !== $customer->id) {
            abort(403, 'Bu şablona erişim yetkiniz yok.');
        }

        // Şablon verilerini kullanarak sipariş oluştur
        $orderData = [
            'pickup_address' => $orderTemplate->pickup_address,
            'delivery_address' => $orderTemplate->delivery_address,
            'planned_delivery_date' => now()->addDays(1),
            'total_weight' => $orderTemplate->total_weight,
            'total_volume' => $orderTemplate->total_volume,
            'is_dangerous' => $orderTemplate->is_dangerous,
            'notes' => $orderTemplate->notes,
        ];

        $order = $this->orderService->create($orderData, $user);
        $order->update(['customer_id' => $customer->id]);

        return redirect()
            ->route('customer.orders.show', $order)
            ->with('success', 'Sipariş şablondan başarıyla oluşturuldu.');
    }

    /**
     * Sipariş şablonu sil.
     */
    public function deleteOrderTemplate(OrderTemplate $orderTemplate): RedirectResponse
    {
        $user = Auth::user();
        
        // Permission kontrolü
        if (!$user->hasPermission('customer.portal.order-templates.manage')) {
            abort(403, 'Sipariş şablonları yönetme yetkiniz bulunmamaktadır.');
        }

        $customer = Customer::where('email', $user->email)->first();

        if (!$customer || $orderTemplate->customer_id !== $customer->id) {
            abort(403, 'Bu şablona erişim yetkiniz yok.');
        }

        $orderTemplate->delete();

        return back()->with('success', 'Sipariş şablonu başarıyla silindi.');
    }
}
