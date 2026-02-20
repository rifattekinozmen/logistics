<?php

use App\Models\Order;
use App\Sap\Models\SapDocument;
use App\Sap\Models\SapSyncLog;
use App\Sap\Services\SapDocumentService;

it('registers an order as a sap document with pending status', function () {
    $service = app(SapDocumentService::class);
    $order = Order::factory()->create(['status' => 'pending']);

    $doc = $service->registerOrder($order);

    expect($doc)->toBeInstanceOf(SapDocument::class)
        ->and($doc->sap_doc_type)->toBe('TA')
        ->and($doc->sync_status)->toBe('pending')
        ->and($doc->local_model_type)->toBe(Order::class)
        ->and($doc->local_model_id)->toBe($order->id);
});

it('marks sap document as synced with sap doc number', function () {
    $service = app(SapDocumentService::class);
    $order = Order::factory()->create(['status' => 'pending']);

    $doc = $service->registerOrder($order);
    $synced = $service->markSynced($doc, '0000012345', '2026');

    expect($synced->sync_status)->toBe('synced')
        ->and($synced->sap_doc_number)->toBe('0000012345')
        ->and($synced->sap_doc_year)->toBe('2026')
        ->and($synced->last_synced_at)->not->toBeNull()
        ->and($synced->isSynced())->toBeTrue();
});

it('marks sap document as failed with error message', function () {
    $service = app(SapDocumentService::class);
    $order = Order::factory()->create(['status' => 'pending']);

    $doc = $service->registerOrder($order);
    $failed = $service->markFailed($doc, 'RFC bağlantı hatası', 500);

    expect($failed->sync_status)->toBe('error')
        ->and($failed->sync_error)->toBe('RFC bağlantı hatası')
        ->and($failed->hasFailed())->toBeTrue();
});

it('creates sync log when marking synced', function () {
    $service = app(SapDocumentService::class);
    $order = Order::factory()->create(['status' => 'pending']);

    $doc = $service->registerOrder($order);
    $service->markSynced($doc, '0000099999');

    expect(SapSyncLog::where('sap_document_id', $doc->id)->exists())->toBeTrue();
});

it('does not duplicate sap document when registering same order twice', function () {
    $service = app(SapDocumentService::class);
    $order = Order::factory()->create(['status' => 'pending']);

    $doc1 = $service->registerOrder($order);
    $doc2 = $service->registerOrder($order);

    expect($doc1->id)->toBe($doc2->id)
        ->and(SapDocument::where('local_model_id', $order->id)->where('sap_doc_type', 'TA')->count())->toBe(1);
});
