<?php

use App\Models\Company;

it('günlük bildirim komutu çalışır', function () {
    Company::factory()->create(['is_active' => true]);

    $result = $this->artisan('notifications:send-daily');

    $result->assertSuccessful();
});
