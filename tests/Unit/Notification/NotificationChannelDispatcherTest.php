<?php

use App\Notification\Services\NotificationChannelDispatcher;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    config(['notifications.channels.document_expiry' => ['mail', 'sms']]);
    config(['notifications.sms.admin_phone' => null]);
    config(['notifications.whatsapp.admin_phone' => null]);
    Cache::flush();
});

it('does not throw when admin phone is not set', function () {
    $dispatcher = app(NotificationChannelDispatcher::class);
    $dispatcher->sendForScenario('document_expiry', 'document_expiry', [
        'days_until' => 7,
        'count' => 5,
        'summary' => '5 belge 7 gün içinde süresi dolacak.',
    ]);
})->throwsNoExceptions();

it('calls sms channel when admin phone is set and sms is in channels', function () {
    config(['notifications.channels.payment_due' => ['mail', 'sms', 'whatsapp']]);
    config(['notifications.sms.admin_phone' => '+905551234567']);
    config(['notifications.sms.enabled' => true]);

    $dispatcher = app(NotificationChannelDispatcher::class);
    $dispatcher->sendForScenario('payment_due', 'payment_due', [
        'days_until' => 3,
        'count' => 2,
        'summary' => '2 ödeme 3 gün içinde.',
    ]);
})->throwsNoExceptions();

it('does not send same template to same recipient twice in same day', function () {
    config(['notifications.channels.document_expiry' => ['sms']]);
    config(['notifications.sms.admin_phone' => '+905551234567']);
    config(['notifications.sms.enabled' => true]);

    $key = 'notification_sent:sms:905551234567:document_expiry:'.now()->toDateString();
    Cache::put($key, true, 86400);

    $dispatcher = app(NotificationChannelDispatcher::class);
    $dispatcher->sendForScenario('document_expiry', 'document_expiry', [
        'days_until' => 7,
        'count' => 1,
        'summary' => '1 belge 7 gün içinde.',
    ]);

    expect(Cache::has($key))->toBeTrue();
})->throwsNoExceptions();
