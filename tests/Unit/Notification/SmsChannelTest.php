<?php

use App\Notification\Channels\SmsChannel;
use Illuminate\Support\Facades\Log;

beforeEach(function () {
    config(['notifications.sms.enabled' => false]);
});

it('does not send when sms channel is disabled', function () {
    Log::spy();

    $channel = new SmsChannel;
    $channel->send('+905551234567', 'payment_due', ['amount' => 100]);

    Log::shouldHaveReceived('debug')->once();
});

it('logs when sms channel is enabled', function () {
    config(['notifications.sms.enabled' => true]);
    Log::spy();

    $channel = new SmsChannel;
    $channel->send('+905551234567', 'payment_due', ['amount' => 100]);

    Log::shouldHaveReceived('info')->with('SMS would be sent.', \Mockery::any())->once();
});
