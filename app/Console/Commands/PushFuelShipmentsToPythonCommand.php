<?php

namespace App\Console\Commands;

use App\Integration\Services\PythonBridgeService;
use Illuminate\Console\Command;

class PushFuelShipmentsToPythonCommand extends Command
{
    protected $signature = 'python:push-fuel-shipments {--days=7 : Son N günün verisi}';

    protected $description = 'Yakıt fiyatı özeti ve sevkiyat sayılarını Python ara katmana kuyruğa gönderir (POC).';

    public function handle(PythonBridgeService $pythonBridge): int
    {
        $days = (int) $this->option('days');
        if ($days <= 0) {
            $days = 7;
        }

        $pythonBridge->pushFuelAndShipmentsToPython($days);

        $this->info("Fuel+shipments payload kuyruğa alındı (son {$days} gün).");

        return Command::SUCCESS;
    }
}
