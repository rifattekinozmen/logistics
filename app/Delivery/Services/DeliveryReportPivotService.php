<?php

namespace App\Delivery\Services;

use App\Models\DeliveryImportBatch;
use App\Models\DeliveryReportRow;

class DeliveryReportPivotService
{
    /**
     * Rapor tipine göre pivot/invoice config'ini döndürür.
     *
     * @return array{pivot_dimensions?: array<int, string>, pivot_metrics?: array<int|string, string>, invoice_line_mapping?: array<string, int>}
     */
    public function getReportTypeConfig(DeliveryImportBatch $batch): array
    {
        $types = config('delivery_report.report_types', []);
        if (! $batch->report_type || ! isset($types[$batch->report_type])) {
            return [
                'pivot_dimensions' => [],
                'pivot_metrics' => [],
                'pivot_metric_labels' => [],
                'invoice_line_mapping' => [],
                'material_pivot' => null,
            ];
        }

        $config = $types[$batch->report_type];

        return [
            'pivot_dimensions' => $config['pivot_dimensions'] ?? [],
            'pivot_metrics' => $config['pivot_metrics'] ?? [],
            'pivot_metric_labels' => $config['pivot_metric_labels'] ?? [],
            'invoice_line_mapping' => $config['invoice_line_mapping'] ?? [],
            'material_pivot' => $config['material_pivot'] ?? null,
        ];
    }

    /**
     * Malzeme Pivot Tablosu (Cemiloglu uyumlu): Tarih x Malzeme.
     * Hücre = Geçerli Miktar (ilk). BOŞ-DOLU / DOLU-DOLU Klinker-Cüruf-Petrokok formülü ile hesaplanır.
     *
     * @return array{dates: array<string>, materials: array<array{key: string, label: string}>, rows: array, totals_row: array}
     */
    public function buildMaterialPivot(DeliveryImportBatch $batch): array
    {
        $config = $this->getReportTypeConfig($batch);
        $mp = $config['material_pivot'] ?? null;

        if (! $mp || ! isset($mp['date_index'], $mp['material_code_index'], $mp['quantity_index'])) {
            return [
                'dates' => [],
                'materials' => [],
                'rows' => [],
                'totals_row' => ['material_totals' => [], 'row_total' => 0, 'boş_dolu' => 0, 'dolu_dolu' => 0],
            ];
        }

        $dateIndex = (int) $mp['date_index'];
        $materialCodeIndex = (int) $mp['material_code_index'];
        $materialShortIndex = isset($mp['material_short_text_index']) ? (int) $mp['material_short_text_index'] : null;
        $quantityIndex = (int) $mp['quantity_index'];
        $doluAgirlikIndex = isset($mp['dolu_agirlik_index']) ? (int) $mp['dolu_agirlik_index'] : null;
        $bosAgirlikIndex = isset($mp['bos_agirlik_index']) ? (int) $mp['bos_agirlik_index'] : null;
        $gecerli2Index = isset($mp['gecerli_miktar_2_index']) ? (int) $mp['gecerli_miktar_2_index'] : null;
        $firmaMiktariIndex = isset($mp['firma_miktari_index']) ? (int) $mp['firma_miktari_index'] : null;

        $rows = $batch->reportRows()->orderBy('row_index')->get();
        $pivotData = [];

        foreach ($rows as $row) {
            $data = $row->row_data ?? [];
            $date = $this->normalizeDateForPivot((string) ($data[$dateIndex] ?? ''));
            $code = trim((string) ($data[$materialCodeIndex] ?? ''));
            $short = $materialShortIndex !== null ? trim((string) ($data[$materialShortIndex] ?? '')) : '';
            $matKey = ($code !== '' && $short !== '') ? $code.' | '.$short : ($code ?: $short ?: '-');

            if ($date === '' || $matKey === '' || $matKey === '-') {
                continue;
            }

            $qty = $this->extractQuantity($data[$quantityIndex] ?? null);
            if ($qty === null) {
                continue;
            }

            if (! isset($pivotData[$date][$matKey])) {
                $pivotData[$date][$matKey] = [
                    'quantity' => 0,
                    'dolu_agirlik' => 0,
                    'bos_agirlik' => 0,
                    'gecerli_miktar_1' => 0,
                    'gecerli_miktar_2' => 0,
                    'firma_miktari' => 0,
                ];
            }

            $pivotData[$date][$matKey]['quantity'] += $qty;
            $pivotData[$date][$matKey]['gecerli_miktar_1'] += $qty;

            if ($doluAgirlikIndex !== null) {
                $pivotData[$date][$matKey]['dolu_agirlik'] += $this->extractQuantity($data[$doluAgirlikIndex] ?? null) ?? 0;
            }
            if ($bosAgirlikIndex !== null) {
                $pivotData[$date][$matKey]['bos_agirlik'] += $this->extractQuantity($data[$bosAgirlikIndex] ?? null) ?? 0;
            }
            if ($gecerli2Index !== null) {
                $pivotData[$date][$matKey]['gecerli_miktar_2'] += $this->extractQuantity($data[$gecerli2Index] ?? null) ?? 0;
            }
            if ($firmaMiktariIndex !== null) {
                $pivotData[$date][$matKey]['firma_miktari'] += $this->extractQuantity($data[$firmaMiktariIndex] ?? null) ?? 0;
            }
        }

        $pivotData = $this->sortPivotDataByDate($pivotData);

        $totalsMaterial = [];
        $totalsBoşDolu = 0;
        $totalsDoluDolu = 0;
        $outRows = [];

        foreach ($pivotData as $date => $materials) {
            ksort($pivotData[$date]);
            $satirToplami = 0;
            foreach ($pivotData[$date] as $values) {
                $satirToplami += $values['quantity'] ?? 0;
            }

            $klinkerQuantity = 0;
            $curufQuantity = 0;
            $petrokokQuantity = 0;
            foreach ($pivotData[$date] as $materialKey => $values) {
                $q = $values['quantity'] ?? 0;
                $upper = mb_strtoupper($materialKey);
                $parts = explode('|', $upper);
                $materialCode = trim($parts[0] ?? '');
                $materialShort = trim($parts[1] ?? '');
                if ((stripos($materialCode, 'KLINKER') !== false || stripos($materialShort, 'KLINKER') !== false) &&
                    (stripos($materialCode, 'GRİ') !== false || stripos($materialCode, 'GRI') !== false || stripos($materialShort, 'GRİ') !== false || stripos($materialShort, 'GRI') !== false)) {
                    $klinkerQuantity = $q;
                } elseif (stripos($materialCode, 'CÜRUF') !== false || stripos($materialCode, 'CURUF') !== false || stripos($materialShort, 'CÜRUF') !== false || stripos($materialShort, 'CURUF') !== false) {
                    $curufQuantity = $q;
                } elseif (stripos($materialCode, 'PETROKOK') !== false || stripos($materialCode, 'P.KOK') !== false || stripos($materialShort, 'PETROKOK') !== false || stripos($materialShort, 'P.KOK') !== false || stripos($materialCode, 'MS') !== false || stripos($materialShort, 'MS') !== false) {
                    $petrokokQuantity = $q;
                }
            }

            $this->applyMaterialMatchingLogic($pivotData[$date], $satirToplami);

            $satirBosDoluMalzeme = '--';
            $bosDoluMalzemeler = [];
            foreach ($pivotData[$date] as $values) {
                $calc = $values['bos_dolu_malzeme_calculated'] ?? null;
                if ($calc !== null && $calc !== '--' && ! in_array($calc, $bosDoluMalzemeler, true)) {
                    $bosDoluMalzemeler[] = $calc;
                }
            }
            if ($bosDoluMalzemeler !== []) {
                $satirBosDoluMalzeme = implode('+', $bosDoluMalzemeler);
            } else {
                $rowDolu = 0;
                $rowBos = 0;
                $rowGecerli2 = 0;
                $rowFirma = 0;
                foreach ($pivotData[$date] as $values) {
                    $rowDolu += $values['dolu_agirlik'] ?? 0;
                    $rowBos += $values['bos_agirlik'] ?? 0;
                    $rowGecerli2 += $values['gecerli_miktar_2'] ?? 0;
                    $rowFirma += $values['firma_miktari'] ?? 0;
                }
                if (abs($rowDolu - ($rowFirma + $rowGecerli2)) < 0.01) {
                    $satirBosDoluMalzeme = '--';
                } elseif ($rowDolu > ($rowFirma + $rowGecerli2)) {
                    $satirBosDoluMalzeme = 'Klinker(Gri)';
                } elseif ($rowFirma < 0.01) {
                    $satirBosDoluMalzeme = 'Curuf';
                } elseif ($satirToplami <= $rowFirma) {
                    $satirBosDoluMalzeme = 'P.kok';
                } else {
                    $satirBosDoluMalzeme = 'P.kok+Curuf';
                }
            }

            $doluDoluSatir = 2 * min($klinkerQuantity, $curufQuantity);
            $bosDoluSatir = abs($klinkerQuantity - $curufQuantity);
            if ($satirBosDoluMalzeme === 'P.kok' || $satirBosDoluMalzeme === 'Klinker(Gri)+P.kok') {
                $bosDoluSatir += $petrokokQuantity;
            }

            foreach ($pivotData[$date] as $materialKey => $values) {
                $pivotData[$date][$materialKey]['bos_dolu_tasinan'] = $bosDoluSatir;
                $pivotData[$date][$materialKey]['dolu_dolu_tasinan'] = $doluDoluSatir;
                $pivotData[$date][$materialKey]['bos_dolu_malzeme'] = $satirBosDoluMalzeme;
            }

            $allMatList = $this->collectAllMaterialKeys($pivotData);
            $materialTotals = [];
            $rowTotal = 0;
            foreach ($allMatList as $m) {
                $mKey = $m['key'];
                $val = $pivotData[$date][$mKey]['quantity'] ?? 0;
                $materialTotals[$mKey] = $val;
                $rowTotal += $val;
                $totalsMaterial[$mKey] = ($totalsMaterial[$mKey] ?? 0) + $val;
            }
            $outRows[] = [
                'tarih' => $date,
                'material_totals' => $materialTotals,
                'row_total' => $rowTotal,
                'boş_dolu' => $bosDoluSatir,
                'dolu_dolu' => $doluDoluSatir,
                'malzeme_kisa_metni' => $satirBosDoluMalzeme,
            ];
            $totalsBoşDolu += $bosDoluSatir;
            $totalsDoluDolu += $doluDoluSatir;
        }

        $allMaterials = $this->collectAllMaterialKeys($pivotData);
        $allMaterials = $this->reorderMaterialsCemilogluStyle($allMaterials);
        $grandTotal = array_sum($totalsMaterial);

        return [
            'dates' => array_keys($pivotData),
            'materials' => $allMaterials,
            'rows' => $outRows,
            'totals_row' => [
                'material_totals' => $totalsMaterial,
                'row_total' => $grandTotal,
                'boş_dolu' => $totalsBoşDolu,
                'dolu_dolu' => $totalsDoluDolu,
            ],
        ];
    }

    /**
     * Tüm tarihlerdeki malzeme anahtarlarını toplar (sıralı, benzersiz).
     *
     * @param  array<string, array<string, array>>  $pivotData
     * @return array<int, array{key: string, label: string}>
     */
    protected function collectAllMaterialKeys(array $pivotData): array
    {
        $keys = [];
        foreach ($pivotData as $materials) {
            foreach (array_keys($materials) as $k) {
                $keys[$k] = ['key' => $k, 'label' => $k];
            }
        }
        ksort($keys);

        return array_values($keys);
    }

    /**
     * Cemiloglu sırası: Cüruf, Petrokok yer değiştirir (Cüruf önce).
     *
     * @param  array<int, array{key: string, label: string}>  $materials
     * @return array<int, array{key: string, label: string}>
     */
    protected function reorderMaterialsCemilogluStyle(array $materials): array
    {
        $curufIndex = null;
        $petrokokIndex = null;
        foreach ($materials as $i => $m) {
            $upper = mb_strtoupper($m['key']);
            if ($curufIndex === null && (stripos($upper, 'CÜRUF') !== false || stripos($upper, 'CURUF') !== false)) {
                $curufIndex = $i;
            }
            if ($petrokokIndex === null && (stripos($upper, 'PETROKOK') !== false || stripos($upper, 'P.KOK') !== false)) {
                $petrokokIndex = $i;
            }
        }
        if ($curufIndex !== null && $petrokokIndex !== null) {
            $t = $materials[$curufIndex];
            $materials[$curufIndex] = $materials[$petrokokIndex];
            $materials[$petrokokIndex] = $t;
        }

        return $materials;
    }

    /**
     * Klinker (Gri) - CÜRUF - Petrokok(MS) eşleştirme mantığı (Cemiloglu).
     *
     * @param  array<string, array>  $materials
     */
    protected function applyMaterialMatchingLogic(array &$materials, float $satirToplami): void
    {
        $klinkerGri = null;
        $curuf = null;
        $petrokok = null;

        foreach ($materials as $materialKey => $values) {
            $upper = mb_strtoupper($materialKey);
            $parts = explode('|', $upper);
            $materialCode = trim($parts[0] ?? '');
            $materialShort = trim($parts[1] ?? '');

            if ((stripos($materialCode, 'KLINKER') !== false || stripos($materialShort, 'KLINKER') !== false) &&
                (stripos($materialCode, 'GRİ') !== false || stripos($materialCode, 'GRI') !== false || stripos($materialShort, 'GRİ') !== false || stripos($materialShort, 'GRI') !== false)) {
                $klinkerGri = ['key' => $materialKey, 'values' => &$materials[$materialKey]];
            } elseif (stripos($materialCode, 'CÜRUF') !== false || stripos($materialCode, 'CURUF') !== false || stripos($materialShort, 'CÜRUF') !== false || stripos($materialShort, 'CURUF') !== false) {
                $curuf = ['key' => $materialKey, 'values' => &$materials[$materialKey]];
            } elseif (stripos($materialCode, 'PETROKOK') !== false || stripos($materialCode, 'P.KOK') !== false || stripos($materialShort, 'PETROKOK') !== false || stripos($materialShort, 'P.KOK') !== false || stripos($materialCode, 'MS') !== false || stripos($materialShort, 'MS') !== false) {
                $petrokok = ['key' => $materialKey, 'values' => &$materials[$materialKey]];
            }
        }

        if ($klinkerGri === null || $curuf === null || $petrokok === null) {
            return;
        }

        $klinkerQuantity = $klinkerGri['values']['quantity'] ?? 0;
        $curufQuantity = $curuf['values']['quantity'] ?? 0;
        $petrokokQuantity = $petrokok['values']['quantity'] ?? 0;

        $curufKucuk = $curufQuantity <= $petrokokQuantity;
        $kucukMalzemeQuantity = $curufKucuk ? $curufQuantity : $petrokokQuantity;

        if ($curufKucuk) {
            $curufDoluDolu = $curufQuantity;
            $curufBosDolu = 0;
            $petrokokDoluDolu = 0;
            $petrokokBosDolu = $petrokokQuantity;
        } else {
            $petrokokDoluDolu = $petrokokQuantity;
            $petrokokBosDolu = 0;
            $curufDoluDolu = 0;
            $curufBosDolu = $curufQuantity;
        }

        $klinkerDoluDolu = min($klinkerQuantity, $kucukMalzemeQuantity);
        $klinkerBosDolu = $klinkerQuantity - $klinkerDoluDolu;

        $klinkerGri['values']['bos_dolu_malzeme_calculated'] = $klinkerBosDolu > 0 ? 'Klinker(Gri)' : '--';
        $curuf['values']['bos_dolu_malzeme_calculated'] = $curufBosDolu > 0 ? 'Curuf' : '--';
        $petrokok['values']['bos_dolu_malzeme_calculated'] = $petrokokBosDolu > 0 ? 'P.kok' : '--';
    }

    /**
     * Miktar değerini sayıya çevirir (virgül/nokta destekli).
     */
    protected function extractQuantity(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }
        if (is_numeric($value)) {
            return (float) $value;
        }
        $candidate = str_replace('.', '', (string) $value);
        $candidate = str_replace(',', '.', $candidate);

        return is_numeric($candidate) ? (float) $candidate : null;
    }

    /**
     * Tarih değerini pivot için normalize eder (d.m.Y). Gruplama her zaman tarihe göre yapılır.
     * Excel seri, d.m.Y, d.m.Y H:i, Y-m-d vb. desteklenir; tarih+saat verilirse sadece tarih kısmı alınır.
     */
    protected function normalizeDateForPivot(mixed $value): string
    {
        $value = $value === null ? '' : trim((string) $value);
        if ($value === '') {
            return '';
        }

        $numericValue = null;
        if (is_numeric($value)) {
            $numericValue = (float) $value;
        } else {
            $candidate = str_replace(',', '.', $value);
            if (is_numeric($candidate)) {
                $numericValue = (float) $candidate;
            }
        }

        if ($numericValue !== null && $numericValue >= 1 && $numericValue < 2958466 && class_exists(\PhpOffice\PhpSpreadsheet\Shared\Date::class)) {
            $prev = \PhpOffice\PhpSpreadsheet\Shared\Date::getExcelCalendar();
            \PhpOffice\PhpSpreadsheet\Shared\Date::setExcelCalendar(\PhpOffice\PhpSpreadsheet\Shared\Date::CALENDAR_WINDOWS_1900);
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($numericValue);
                $year = (int) $dt->format('Y');
                $nowYear = (int) date('Y');
                if ($year > $nowYear + 1 || $year < $nowYear - 2) {
                    \PhpOffice\PhpSpreadsheet\Shared\Date::setExcelCalendar(\PhpOffice\PhpSpreadsheet\Shared\Date::CALENDAR_MAC_1904);
                    $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($numericValue);
                }

                return $dt->format('d.m.Y');
            } catch (\Throwable) {
            } finally {
                \PhpOffice\PhpSpreadsheet\Shared\Date::setExcelCalendar($prev);
            }
        }

        $dt = \DateTime::createFromFormat('d.m.Y g:i:s A', $value)
            ?: \DateTime::createFromFormat('d.m.Y g:i A', $value)
            ?: \DateTime::createFromFormat('d.m.Y H:i', $value)
            ?: \DateTime::createFromFormat('d.m.Y H:i:s', $value)
            ?: \DateTime::createFromFormat('d.m.Y', $value)
            ?: \DateTime::createFromFormat('Y-m-d', $value)
            ?: \DateTime::createFromFormat('d/m/Y', $value)
            ?: \DateTime::createFromFormat('m/d/Y', $value)
            ?: \DateTime::createFromFormat('n/j/Y', $value)
            ?: @\DateTime::createFromFormat('Y-m-d H:i:s', $value);
        if ($dt) {
            return $dt->format('d.m.Y');
        }

        if (preg_match('/^(\d{1,2}\.\d{1,2}\.\d{4})/', $value, $m)) {
            return $m[1];
        }

        return $value;
    }

    /**
     * Pivot verisini tarih key'lerine göre kronolojik sıralar (d.m.Y).
     *
     * @param  array<string, array<string, array>>  $pivotData
     * @return array<string, array<string, array>>
     */
    protected function sortPivotDataByDate(array $pivotData): array
    {
        uksort($pivotData, function (string $a, string $b): int {
            $dtA = \DateTime::createFromFormat('d.m.Y', $a);
            $dtB = \DateTime::createFromFormat('d.m.Y', $b);
            if (! $dtA || ! $dtB) {
                return strcmp($a, $b);
            }

            return $dtA->getTimestamp() <=> $dtB->getTimestamp();
        });

        return $pivotData;
    }

    /**
     * Batch'ten pivot özet tablosu üretir.
     * Config'teki pivot_dimensions ile gruplar, pivot_metrics ile toplar/sayar.
     *
     * @param  array<string>|null  $groupByDimensionKeys  Hangi boyutlara göre gruplanacak (null = hepsi)
     * @return array<int, array<string, mixed>>
     */
    public function buildPivot(DeliveryImportBatch $batch, ?array $groupByDimensionKeys = null): array
    {
        $config = $this->getReportTypeConfig($batch);
        $dimensions = $config['pivot_dimensions'];
        $metrics = $config['pivot_metrics'];
        $metricLabels = $config['pivot_metric_labels'] ?? [];

        if ($dimensions === [] || $metrics === []) {
            return [];
        }

        $groupBy = $groupByDimensionKeys !== null
            ? $groupByDimensionKeys
            : array_values($dimensions);

        $rows = $batch->reportRows()->orderBy('row_index')->get();
        $aggregated = [];

        foreach ($rows as $row) {
            $data = $row->row_data ?? [];
            $groupKeyParts = [];
            foreach ($groupBy as $dimKey) {
                $dimIndex = array_search($dimKey, $dimensions, true);
                if ($dimIndex !== false) {
                    $groupKeyParts[] = $data[$dimIndex] ?? '';
                }
            }
            $groupKey = implode('|', $groupKeyParts);

            if (! isset($aggregated[$groupKey])) {
                $aggregated[$groupKey] = [];
                foreach ($groupBy as $dimKey) {
                    $dimIndex = array_search($dimKey, $dimensions, true);
                    if ($dimIndex !== false) {
                        $aggregated[$groupKey][$dimKey] = $data[$dimIndex] ?? '';
                    }
                }
                foreach (array_keys($metrics) as $metricKey) {
                    if ($metricKey === 'rows') {
                        $aggregated[$groupKey]['_count_rows'] = 0;
                    } else {
                        $aggregated[$groupKey]['_sum_'.$metricKey] = 0;
                    }
                }
            }

            foreach ($metrics as $metricIndex => $metricType) {
                if ($metricIndex === 'rows') {
                    $aggregated[$groupKey]['_count_rows']++;
                } elseif ($metricType === 'sum' && isset($data[$metricIndex])) {
                    $val = $data[$metricIndex];
                    $aggregated[$groupKey]['_sum_'.$metricIndex] += is_numeric($val) ? (float) $val : 0;
                }
            }
        }

        $result = [];
        foreach ($aggregated as $row) {
            $out = [];
            foreach ($groupBy as $dimKey) {
                $out[$dimKey] = $row[$dimKey] ?? '';
            }
            foreach ($metrics as $metricIndex => $metricType) {
                $label = $metricLabels[$metricIndex] ?? ('Metrik '.$metricIndex);
                if ($metricIndex === 'rows') {
                    $out[$label] = $row['_count_rows'] ?? 0;
                } else {
                    $out[$label] = $row['_sum_'.$metricIndex] ?? 0;
                }
            }
            $result[] = $out;
        }

        return $result;
    }

    /**
     * Batch'ten fatura kalemleri listesi üretir.
     * invoice_line_mapping ile row_data'dan alanlar alınır; istenirse irsaliye_no + malzeme_kodu ile gruplanıp miktar toplanır.
     *
     * @return array<int, array<string, mixed>>
     */
    public function buildInvoiceLines(DeliveryImportBatch $batch, bool $groupByIrsaliyeAndMaterial = true): array
    {
        $config = $this->getReportTypeConfig($batch);
        $mapping = $config['invoice_line_mapping'];

        if ($mapping === []) {
            return [];
        }

        $rows = $batch->reportRows()->orderBy('row_index')->get();
        $lines = [];

        foreach ($rows as $row) {
            $data = $row->row_data ?? [];
            $line = [];
            foreach ($mapping as $fieldName => $index) {
                $line[$fieldName] = trim((string) ($data[$index] ?? ''));
            }
            $lines[] = $line;
        }

        if (! $groupByIrsaliyeAndMaterial) {
            return $lines;
        }

        return $this->groupInvoiceLinesByIrsaliyeAndMaterial($lines);
    }

    /**
     * Fatura kalemlerini irsaliye_no + malzeme_kodu ile gruplayıp miktarı toplar.
     *
     * @param  array<int, array<string, mixed>>  $lines
     * @return array<int, array<string, mixed>>
     */
    protected function groupInvoiceLinesByIrsaliyeAndMaterial(array $lines): array
    {
        $grouped = [];
        foreach ($lines as $line) {
            $irsaliye = $line['irsaliye_no'] ?? '';
            $malzeme = $line['malzeme_kodu'] ?? '';
            $key = $irsaliye.'|'.$malzeme;

            if (! isset($grouped[$key])) {
                $grouped[$key] = $line;
                $grouped[$key]['miktar'] = is_numeric($line['miktar'] ?? '') ? (float) $line['miktar'] : 0;
            } else {
                $m = $line['miktar'] ?? '';
                $grouped[$key]['miktar'] += is_numeric($m) ? (float) $m : 0;
            }
        }

        return array_values($grouped);
    }
}
