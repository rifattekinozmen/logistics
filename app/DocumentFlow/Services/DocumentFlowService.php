<?php

namespace App\DocumentFlow\Services;

use App\DocumentFlow\Models\DocumentFlow;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Shipment;
use DB;
use Illuminate\Support\Collection;

class DocumentFlowService
{
    /**
     * Yeni bir sipariş oluşturulduğunda zincirin ilk halkasını oluşturur.
     */
    public function initializeOrderChain(Order $order): DocumentFlow
    {
        return DocumentFlow::create([
            'company_id' => $order->customer?->businessPartner?->company_id ?? $order->company_id ?? 0,
            'source_type' => Order::class,
            'source_id' => $order->id,
            'step' => 'order_created',
            'completed_at' => now(),
        ]);
    }

    /**
     * Siparişe sevkiyat atandığında belge zincirini ilerletir.
     */
    public function recordDeliveryStep(Order $order, Shipment $shipment): DocumentFlow
    {
        return DocumentFlow::create([
            'company_id' => $order->customer?->businessPartner?->company_id ?? $order->company_id ?? 0,
            'source_type' => Order::class,
            'source_id' => $order->id,
            'target_type' => Shipment::class,
            'target_id' => $shipment->id,
            'step' => 'delivery_assigned',
            'completed_at' => now(),
        ]);
    }

    /**
     * Sevkiyata fatura/ödeme eklendiğinde zinciri tamamlar.
     */
    public function recordInvoiceStep(Shipment $shipment, Payment $payment): DocumentFlow
    {
        return DocumentFlow::create([
            'company_id' => 0,
            'source_type' => Shipment::class,
            'source_id' => $shipment->id,
            'target_type' => Payment::class,
            'target_id' => $payment->id,
            'step' => 'invoice_created',
            'completed_at' => now(),
        ]);
    }

    /**
     * Herhangi bir belgenin tüm zincirini döner.
     */
    public function getChainFor(string $modelClass, int $modelId): Collection
    {
        return DocumentFlow::where(function ($q) use ($modelClass, $modelId) {
            $q->where('source_type', $modelClass)->where('source_id', $modelId);
        })->orWhere(function ($q) use ($modelClass, $modelId) {
            $q->where('target_type', $modelClass)->where('target_id', $modelId);
        })->orderBy('created_at')->get();
    }

    /**
     * Bir adımı tamamlandı olarak işaretler.
     */
    public function completeStep(DocumentFlow $flow): DocumentFlow
    {
        $flow->update(['completed_at' => now()]);

        return $flow->fresh();
    }

    /**
     * Track a document in the flow system.
     * Creates or updates a document flow record for the given source.
     *
     * @param  string  $sourceType  Source model class (Order::class, Shipment::class, etc.)
     * @param  int  $sourceId  Source model ID
     * @return DocumentFlow Document flow record
     */
    public function trackDocument(string $sourceType, int $sourceId): DocumentFlow
    {
        $documentType = $this->inferDocumentType($sourceType);
        $documentNumber = $this->getDocumentNumber($sourceType, $sourceId);

        return DocumentFlow::firstOrCreate(
            [
                'source_type' => $sourceType,
                'source_id' => $sourceId,
            ],
            [
                'company_id' => $this->getCompanyId($sourceType, $sourceId),
                'step' => $this->getInitialStep($documentType),
                'document_type' => $documentType,
                'document_number' => $documentNumber,
                'status' => 'created',
            ]
        );
    }

    /**
     * Link two document flows (parent → child relationship).
     * Creates a flow record connecting source to target.
     *
     * @param  DocumentFlow  $parent  Parent document flow
     * @param  DocumentFlow  $child  Child document flow
     */
    public function linkDocuments(DocumentFlow $parent, DocumentFlow $child): void
    {
        $child->update([
            'parent_document_flow_id' => $parent->id,
        ]);

        DocumentFlow::create([
            'company_id' => $parent->company_id,
            'source_type' => $parent->source_type,
            'source_id' => $parent->source_id,
            'target_type' => $child->source_type,
            'target_id' => $child->source_id,
            'step' => $this->getLinkStep($parent->document_type, $child->document_type),
            'completed_at' => now(),
        ]);
    }

    /**
     * Get the full document chain for an order.
     * Returns all related documents: Order → Delivery → Invoice.
     *
     * @param  int  $orderId  Order ID
     * @return Collection<int, DocumentFlow> Complete document flow chain
     */
    public function getFullChain(int $orderId): Collection
    {
        // Start with order flows
        $flows = DocumentFlow::where('source_type', Order::class)
            ->where('source_id', $orderId)
            ->orderBy('created_at')
            ->get();

        // Get delivery flows
        $deliveryFlows = DocumentFlow::where('source_type', Shipment::class)
            ->whereIn('source_id', function ($query) use ($orderId) {
                $query->select('id')
                    ->from('shipments')
                    ->where('order_id', $orderId);
            })
            ->orderBy('created_at')
            ->get();

        // Get invoice flows
        $invoiceFlows = DocumentFlow::where('source_type', Payment::class)
            ->whereIn('source_id', function ($query) use ($orderId) {
                $query->select('payments.id')
                    ->from('payments')
                    ->join('orders', function ($join) {
                        $join->on('payments.related_type', '=', DB::raw("'".addslashes(Order::class)."'"))
                            ->on('payments.related_id', '=', 'orders.id');
                    })
                    ->where('orders.id', $orderId);
            })
            ->orderBy('created_at')
            ->get();

        return $flows->merge($deliveryFlows)->merge($invoiceFlows)->sortBy('created_at')->values();
    }

    /**
     * Infer document type from model class.
     *
     * @param  string  $modelClass  Model class name
     * @return string Document type
     */
    protected function inferDocumentType(string $modelClass): string
    {
        return match ($modelClass) {
            Order::class => 'order',
            Shipment::class => 'delivery',
            Payment::class => 'invoice',
            default => 'unknown',
        };
    }

    /**
     * Get document number from model.
     *
     * @param  string  $sourceType  Source model class
     * @param  int  $sourceId  Source model ID
     * @return string|null Document number
     */
    protected function getDocumentNumber(string $sourceType, int $sourceId): ?string
    {
        $model = $sourceType::find($sourceId);

        if (! $model) {
            return null;
        }

        return match ($sourceType) {
            Order::class => $model->order_number ?? null,
            Shipment::class => $model->qr_code ?? null,
            Payment::class => $model->reference_number ?? null,
            default => null,
        };
    }

    /**
     * Get company ID from model.
     *
     * @param  string  $sourceType  Source model class
     * @param  int  $sourceId  Source model ID
     * @return int Company ID
     */
    protected function getCompanyId(string $sourceType, int $sourceId): int
    {
        $model = $sourceType::find($sourceId);

        if (! $model) {
            return 0;
        }

        return match ($sourceType) {
            Order::class => $model->company_id ?? 0,
            Shipment::class => $model->order?->company_id ?? 0,
            default => 0,
        };
    }

    /**
     * Get initial step name for document type.
     *
     * @param  string  $documentType  Document type
     * @return string Initial step name
     */
    protected function getInitialStep(string $documentType): string
    {
        return match ($documentType) {
            'order' => 'order_created',
            'delivery' => 'delivery_assigned',
            'invoice' => 'invoice_created',
            default => 'document_created',
        };
    }

    /**
     * Get link step name between two document types.
     *
     * @param  string  $parentType  Parent document type
     * @param  string  $childType  Child document type
     * @return string Link step name
     */
    protected function getLinkStep(string $parentType, string $childType): string
    {
        return "{$parentType}_to_{$childType}";
    }
}
