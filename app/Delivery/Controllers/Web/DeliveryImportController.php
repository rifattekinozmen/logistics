<?php

namespace App\Delivery\Controllers\Web;

use App\Delivery\Jobs\ProcessDeliveryImportJob;
use App\Http\Controllers\Controller;
use App\Models\DeliveryImportBatch;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DeliveryImportController extends Controller
{
    /**
     * Liste: Yüklenmiş teslimat import batch'leri.
     */
    public function index(): View
    {
        $batches = DeliveryImportBatch::query()
            ->latest()
            ->paginate(20);

        return view('admin.delivery-imports.index', compact('batches'));
    }

    /**
     * Yeni Excel yükleme formu.
     */
    public function create(): View
    {
        return view('admin.delivery-imports.create');
    }

    /**
     * Excel dosyasını al, batch kaydı oluştur ve işlenmek üzere kuyruğa hazırla.
     *
     * Not: Şimdilik sadece dosyayı ve batch kaydını oluşturuyoruz.
     * Gerçek Excel parse ve otomatik sipariş oluşturma akışı ayrı Job/Service ile eklenecek.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,csv,txt|max:20480',
        ]);

        $user = Auth::user();
        $company = $user->activeCompany();

        if (! $company) {
            return back()->withErrors([
                'file' => 'Aktif bir firma seçmeden teslimat numarası yükleyemezsiniz.',
            ]);
        }

        $file = $request->file('file');
        $path = $file->store('delivery-imports', 'private');

        $batch = DeliveryImportBatch::query()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'total_rows' => 0,
            'processed_rows' => 0,
            'successful_rows' => 0,
            'failed_rows' => 0,
            'status' => 'pending',
            'imported_by' => $user->id,
        ]);

        // Job'u dispatch et
        ProcessDeliveryImportJob::dispatch($batch, $company);

        return redirect()
            ->route('admin.delivery-imports.show', $batch)
            ->with('success', 'Dosya başarıyla yüklendi. İşleme alınmak üzere kuyruğa eklendi.');
    }

    /**
     * Tek bir batch ve ilişkili teslimat numaralarını göster.
     */
    public function show(DeliveryImportBatch $batch): View
    {
        $deliveryNumbers = $batch->deliveryNumbers()
            ->latest()
            ->paginate(25);

        $fileExists = $batch->file_path
            ? Storage::disk('private')->exists($batch->file_path)
            : false;

        return view('admin.delivery-imports.show', [
            'batch' => $batch,
            'deliveryNumbers' => $deliveryNumbers,
            'fileExists' => $fileExists,
        ]);
    }
}

