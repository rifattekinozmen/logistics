<?php

namespace App\Customer\Controllers\Web;

use App\Customer\Concerns\ResolvesCustomerFromUser;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Document;
use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class DocumentController extends Controller
{
    use ResolvesCustomerFromUser;

    public function documents(Request $request): View|StreamedResponse|RedirectResponse
    {
        $this->authorizeCustomerPermission('customer.portal.documents.view');
        $customer = $this->resolveCustomer();

        $orderIds = Order::where('customer_id', $customer->id)->pluck('id');

        $query = Document::where('documentable_type', Order::class)
            ->whereIn('documentable_id', $orderIds);

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

        if ($request->has('download_selected') && $request->filled('selected_documents')) {
            return $this->downloadMultipleDocuments($request->selected_documents, $customer);
        }

        $documents = $query->latest()->paginate(20);

        $categories = Document::where('documentable_type', Order::class)
            ->whereIn('documentable_id', $orderIds)
            ->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();

        $orders = Order::where('customer_id', $customer->id)
            ->orderBy('order_number')
            ->get(['id', 'order_number']);

        return view('customer.documents.index', compact('documents', 'categories', 'orders'));
    }

    protected function downloadMultipleDocuments(array $documentIds, Customer $customer): StreamedResponse|RedirectResponse
    {
        $this->authorizeCustomerPermission('customer.portal.documents.download');

        $orderIds = Order::where('customer_id', $customer->id)->pluck('id');

        $documents = Document::where('documentable_type', Order::class)
            ->whereIn('documentable_id', $orderIds)
            ->whereIn('id', $documentIds)
            ->get();

        if ($documents->isEmpty()) {
            return back()->withErrors(['documents' => 'İndirilecek belge bulunamadı.']);
        }

        $zipFileName = 'belgeler_'.now()->format('Y-m-d_His').'.zip';
        $zipPath = storage_path('app/temp/'.$zipFileName);

        if (! file_exists(storage_path('app/temp'))) {
            mkdir(storage_path('app/temp'), 0755, true);
        }

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
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

    public function downloadDocument(Document $document): StreamedResponse|RedirectResponse
    {
        $this->authorizeCustomerPermission('customer.portal.documents.download');
        $customer = $this->resolveCustomer();

        if ($document->documentable_type === Order::class) {
            $order = Order::find($document->documentable_id);
            if (! $order || $order->customer_id !== $customer->id) {
                abort(403, 'Bu belgeye erişim yetkiniz yok.');
            }
        }

        if (! Storage::disk('public')->exists($document->file_path)) {
            return back()->withErrors(['document' => 'Belge dosyası bulunamadı.']);
        }

        return Storage::disk('public')->download($document->file_path, $document->name);
    }
}
