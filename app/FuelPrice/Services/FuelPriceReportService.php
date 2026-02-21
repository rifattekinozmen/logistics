<?php

namespace App\FuelPrice\Services;

use App\Models\FuelPrice;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class FuelPriceReportService
{
    /**
     * Generate weekly fuel price report.
     */
    public function generateWeeklyReport(Carbon $startDate, Carbon $endDate): string
    {
        $prices = FuelPrice::query()
            ->whereBetween('price_date', [$startDate, $endDate])
            ->orderBy('price_date')
            ->get();

        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Haftalık Motorin Fiyat Raporu');
        $sheet->setCellValue('A2', $startDate->format('d.m.Y').' - '.$endDate->format('d.m.Y'));

        $sheet->setCellValue('A4', 'Tarih');
        $sheet->setCellValue('B4', 'Satın Alma Fiyatı (TL/L)');
        $sheet->setCellValue('C4', 'İstasyon Fiyatı (TL/L)');
        $sheet->setCellValue('D4', 'Fark (TL/L)');
        $sheet->setCellValue('E4', 'Fark (%)');

        $row = 5;
        foreach ($prices as $price) {
            $diff = $price->station_price - $price->purchase_price;
            $diffPercent = $price->purchase_price > 0
                ? round(($diff / $price->purchase_price) * 100, 2)
                : 0;

            $sheet->setCellValue('A'.$row, $price->price_date->format('d.m.Y'));
            $sheet->setCellValue('B'.$row, number_format($price->purchase_price, 2));
            $sheet->setCellValue('C'.$row, number_format($price->station_price, 2));
            $sheet->setCellValue('D'.$row, number_format($diff, 2));
            $sheet->setCellValue('E'.$row, $diffPercent.'%');

            $row++;
        }

        $avgPurchase = $prices->avg('purchase_price');
        $avgStation = $prices->avg('station_price');
        $avgDiff = $avgStation - $avgPurchase;

        $row++;
        $sheet->setCellValue('A'.$row, 'Haftalık Ortalama');
        $sheet->setCellValue('B'.$row, number_format($avgPurchase, 2));
        $sheet->setCellValue('C'.$row, number_format($avgStation, 2));
        $sheet->setCellValue('D'.$row, number_format($avgDiff, 2));

        $filename = 'motorin_rapor_'.$startDate->format('Y_m_d').'.xlsx';
        $filepath = storage_path('app/reports/'.$filename);

        if (! is_dir(storage_path('app/reports'))) {
            mkdir(storage_path('app/reports'), 0755, true);
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filepath);

        return $filepath;
    }

    /**
     * Get weekly summary statistics.
     */
    public function getWeeklySummary(Carbon $startDate, Carbon $endDate): array
    {
        $prices = FuelPrice::query()
            ->whereBetween('price_date', [$startDate, $endDate])
            ->get();

        if ($prices->isEmpty()) {
            return [
                'week' => $startDate->format('d.m.Y').' - '.$endDate->format('d.m.Y'),
                'average_purchase' => 0,
                'average_station' => 0,
                'total_days' => 0,
                'trend' => 'stable',
            ];
        }

        $avgPurchase = $prices->avg('purchase_price');
        $avgStation = $prices->avg('station_price');

        $firstPrice = $prices->first()->purchase_price;
        $lastPrice = $prices->last()->purchase_price;
        $priceChange = $lastPrice - $firstPrice;
        $trend = abs($priceChange) < 0.5 ? 'stable' : ($priceChange > 0 ? 'increasing' : 'decreasing');

        return [
            'week' => $startDate->format('d.m.Y').' - '.$endDate->format('d.m.Y'),
            'average_purchase' => round($avgPurchase, 2),
            'average_station' => round($avgStation, 2),
            'average_diff' => round($avgStation - $avgPurchase, 2),
            'total_days' => $prices->count(),
            'trend' => $trend,
            'price_change' => round($priceChange, 2),
        ];
    }
}
