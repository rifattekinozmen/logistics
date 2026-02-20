<?php

namespace App\DocumentFlow\Controllers\Web;

use App\DocumentFlow\Services\DocumentFlowService;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\View\View;

class DocumentFlowController extends Controller
{
    public function __construct(protected DocumentFlowService $documentFlowService) {}

    /**
     * Sipariş için doküman akışını gösterir.
     */
    public function show(Order $order): View
    {
        $chain = $this->documentFlowService->getChainFor(Order::class, $order->id);

        return view('admin.document-flows.show', compact('order', 'chain'));
    }
}
