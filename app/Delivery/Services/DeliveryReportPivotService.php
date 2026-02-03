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
     * Rapor Detayı ile aynı başlık setini döndürür (row_data sütun sırası buna göredir).
     *
     * @return array<int, string>
     */
    protected function getExpectedHeadersForBatch(DeliveryImportBatch $batch): array
    {
        $types = config('delivery_report.report_types', []);
        if ($batch->report_type && isset($types[$batch->report_type]['headers'])) {
            return $types[$batch->report_type]['headers'];
        }

        return config('delivery_report.expected_headers', []);
    }

    /**
     * Rapor Detayı'ndaki "Tarih" (date-only) sütununun row_data index'ini döndürür.
     * Rapor Detayı ile aynı sütunu kullanmak için date_only_column_indices kullanılır.
     */
    protected function resolveDateColumnIndex(DeliveryImportBatch $batch, array $materialPivotConfig): int
    {
        $types = config('delivery_report.report_types', []);
        if ($batch->report_type && isset($types[$batch->report_type]['date_only_column_indices'])) {
            $indices = $types[$batch->report_type]['date_only_column_indices'];
            if ($indices !== [] && isset($indices[0])) {
                return (int) $indices[0];
            }
        }

        $expectedHeaders = $this->getExpectedHeadersForBatch($batch);
        $tarihIndex = array_search('Tarih', $expectedHeaders, true);
        if ($tarihIndex !== false) {
            return (int) $tarihIndex;
        }

        return (int) ($materialPivotConfig['date_index'] ?? 0);
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

        if (! $mp || ! isset($mp['material_code_index'], $mp['quantity_index'])) {
            return [
                'dates' => [],
                'materials' => [],
                'rows' => [],
                'totals_row' => ['material_totals' => [], 'row_total' => 0, 'boş_dolu' => 0, 'dolu_dolu' => 0],
            ];
        }

        $dateIndex = $this->resolveDateColumnIndex($batch, $mp);
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
                    'row_count' => 0,
                    'dolu_agirlik' => 0,
                    'bos_agirlik' => 0,
                    'gecerli_miktar_1' => 0,
                    'gecerli_miktar_2' => 0,
                    'firma_miktari' => 0,
                ];
            }

            $pivotData[$date][$matKey]['quantity'] += $qty;
            $pivotData[$date][$matKey]['row_count'] += 1;
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
        $totalsMaterialCounts = [];
        $totalsBoşDolu = 0;
        $totalsDoluDolu = 0;
        $faturaTotals = [];
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
            $klinkerKey = null;
            $curufKey = null;
            $petrokokKey = null;
            foreach ($pivotData[$date] as $materialKey => $values) {
                $q = $values['quantity'] ?? 0;
                $upper = mb_strtoupper($materialKey);
                $parts = explode('|', $upper);
                $materialCode = trim($parts[0] ?? '');
                $materialShort = trim($parts[1] ?? '');
                if ((stripos($materialCode, 'KLINKER') !== false || stripos($materialShort, 'KLINKER') !== false) &&
                    (stripos($materialCode, 'GRİ') !== false || stripos($materialCode, 'GRI') !== false || stripos($materialShort, 'GRİ') !== false || stripos($materialShort, 'GRI') !== false)) {
                    $klinkerQuantity += $q;
                    $klinkerKey = $materialKey;
                } elseif (stripos($materialCode, 'CÜRUF') !== false || stripos($materialCode, 'CURUF') !== false || stripos($materialShort, 'CÜRUF') !== false || stripos($materialShort, 'CURUF') !== false) {
                    $curufQuantity += $q;
                    $curufKey = $materialKey;
                } elseif (stripos($materialCode, 'PETROKOK') !== false || stripos($materialCode, 'P.KOK') !== false || stripos($materialShort, 'PETROKOK') !== false || stripos($materialShort, 'P.KOK') !== false || stripos($materialCode, 'MS') !== false || stripos($materialShort, 'MS') !== false) {
                    $petrokokQuantity += $q;
                    $petrokokKey = $materialKey;
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
            if ($klinkerQuantity <= 0.001) {
                if ($curufQuantity > 0.001 && $petrokokQuantity > 0.001) {
                    $satirBosDoluMalzeme = 'Petrokok (MS)+Curuf';
                } elseif ($petrokokQuantity > 0.001) {
                    $satirBosDoluMalzeme = 'Petrokok (MS)';
                } elseif ($curufQuantity > 0.001) {
                    $satirBosDoluMalzeme = 'Curuf';
                } else {
                    $satirBosDoluMalzeme = '--';
                }
            } else {
                $onlyOnePartner = ($curufQuantity > 0.001 && $petrokokQuantity <= 0.001)
                    || ($curufQuantity <= 0.001 && $petrokokQuantity > 0.001);
                if ($onlyOnePartner) {
                    $satirBosDoluMalzeme = $curufQuantity > 0.001
                        ? ($klinkerQuantity >= $curufQuantity ? 'Klinker' : 'Curuf')
                        : ($klinkerQuantity >= $petrokokQuantity ? 'Klinker' : 'Petrokok (MS)');
                } elseif ($bosDoluMalzemeler !== []) {
                    $order = ['Petrokok (MS)' => 0, 'Curuf' => 1, 'Klinker(Gri)' => 2, 'Klinker' => 2];
                    usort($bosDoluMalzemeler, fn (string $a, string $b): int => ($order[$a] ?? 3) <=> ($order[$b] ?? 3));
                    $satirBosDoluMalzeme = implode('+', $bosDoluMalzemeler);
                }
            }
            if ($satirBosDoluMalzeme === '--') {
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
                    $satirBosDoluMalzeme = 'Petrokok (MS)';
                } else {
                    $satirBosDoluMalzeme = 'Petrokok (MS)+Curuf';
                }
            }

            $partnerQuantity = $curufQuantity > 0 && $petrokokQuantity > 0
                ? min($curufQuantity, $petrokokQuantity)
                : ($curufQuantity > 0 ? $curufQuantity : $petrokokQuantity);
            $doluDoluSatir = 2 * min($klinkerQuantity, $partnerQuantity);
            if ($klinkerQuantity <= 0.001) {
                $bosDoluSatir = $curufQuantity + $petrokokQuantity;
            } elseif ($curufQuantity > 0 && $petrokokQuantity > 0) {
                $bosDoluSatir = abs($klinkerQuantity - $curufQuantity);
                if (in_array($satirBosDoluMalzeme, ['Petrokok (MS)', 'Klinker(Gri)+Petrokok (MS)', 'Petrokok (MS)+Curuf'], true)) {
                    $bosDoluSatir += $petrokokQuantity;
                }
            } else {
                $bosDoluSatir = $curufQuantity > 0
                    ? abs($klinkerQuantity - $curufQuantity)
                    : abs($klinkerQuantity - $petrokokQuantity);
            }

            foreach ($pivotData[$date] as $materialKey => $values) {
                $pivotData[$date][$materialKey]['bos_dolu_tasinan'] = $bosDoluSatir;
                $pivotData[$date][$materialKey]['dolu_dolu_tasinan'] = $doluDoluSatir;
                $pivotData[$date][$materialKey]['bos_dolu_malzeme'] = $satirBosDoluMalzeme;
            }

            $partnerKey = $curufQuantity > 0 && $petrokokQuantity > 0
                ? ($curufQuantity <= $petrokokQuantity ? $curufKey : $petrokokKey)
                : ($curufQuantity > 0 ? $curufKey : $petrokokKey);
            if ($klinkerKey !== null) {
                $faturaTotals[$klinkerKey] = $faturaTotals[$klinkerKey] ?? ['d_d' => 0, 'b_d' => 0];
                $faturaTotals[$klinkerKey]['d_d'] += $doluDoluSatir;
            }
            if ($partnerKey !== null) {
                $faturaTotals[$partnerKey] = $faturaTotals[$partnerKey] ?? ['d_d' => 0, 'b_d' => 0];
                $faturaTotals[$partnerKey]['d_d'] += $doluDoluSatir;
            }
            $klinkerBd = 0;
            $curufBd = 0;
            $petrokokBd = 0;
            if ($klinkerQuantity <= 0.001) {
                $curufBd = $curufQuantity;
                $petrokokBd = $petrokokQuantity;
            } elseif ($curufQuantity > 0 && $petrokokQuantity > 0) {
                $klinkerBd = $klinkerQuantity > $curufQuantity ? $klinkerQuantity - $curufQuantity : 0;
                $curufBd = $curufQuantity > $klinkerQuantity ? $curufQuantity - $klinkerQuantity : 0;
                if (in_array($satirBosDoluMalzeme, ['Petrokok (MS)', 'Klinker(Gri)+Petrokok (MS)', 'Petrokok (MS)+Curuf'], true)) {
                    $petrokokBd = $petrokokQuantity;
                }
            } else {
                if ($curufQuantity > 0.001) {
                    $klinkerBd = $klinkerQuantity >= $curufQuantity ? $klinkerQuantity - $curufQuantity : 0;
                    $curufBd = $curufQuantity > $klinkerQuantity ? $curufQuantity - $klinkerQuantity : 0;
                } else {
                    $klinkerBd = $klinkerQuantity >= $petrokokQuantity ? $klinkerQuantity - $petrokokQuantity : 0;
                    $petrokokBd = $petrokokQuantity > $klinkerQuantity ? $petrokokQuantity - $klinkerQuantity : 0;
                }
            }
            if ($klinkerKey !== null && $klinkerBd > 0.001) {
                $faturaTotals[$klinkerKey] = $faturaTotals[$klinkerKey] ?? ['d_d' => 0, 'b_d' => 0];
                $faturaTotals[$klinkerKey]['b_d'] += $klinkerBd;
            }
            if ($curufKey !== null && $curufBd > 0.001) {
                $faturaTotals[$curufKey] = $faturaTotals[$curufKey] ?? ['d_d' => 0, 'b_d' => 0];
                $faturaTotals[$curufKey]['b_d'] += $curufBd;
            }
            if ($petrokokKey !== null && $petrokokBd > 0.001) {
                $faturaTotals[$petrokokKey] = $faturaTotals[$petrokokKey] ?? ['d_d' => 0, 'b_d' => 0];
                $faturaTotals[$petrokokKey]['b_d'] += $petrokokBd;
            }

            $allMatList = $this->collectAllMaterialKeys($pivotData);
            $materialTotals = [];
            $materialCounts = [];
            $rowTotal = 0;
            foreach ($allMatList as $m) {
                $mKey = $m['key'];
                $val = $pivotData[$date][$mKey]['quantity'] ?? 0;
                $cnt = $pivotData[$date][$mKey]['row_count'] ?? 0;
                $materialTotals[$mKey] = $val;
                $materialCounts[$mKey] = $cnt;
                $rowTotal += $val;
                $totalsMaterial[$mKey] = ($totalsMaterial[$mKey] ?? 0) + $val;
                $totalsMaterialCounts[$mKey] = ($totalsMaterialCounts[$mKey] ?? 0) + $cnt;
            }
            $outRows[] = [
                'tarih' => $date,
                'material_totals' => $materialTotals,
                'material_counts' => $materialCounts,
                'row_total' => $rowTotal,
                'row_total_count' => array_sum($materialCounts),
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

        $faturaKalemleri = [];
        foreach ($faturaTotals as $matKey => $totals) {
            $label = str_contains($matKey, ' | ') ? str_replace(' | ', ' - ', $matKey) : $matKey;
            if (($totals['d_d'] ?? 0) > 0.001) {
                $faturaKalemleri[] = [
                    'material_key' => $matKey,
                    'material_label' => $label,
                    'tasima_tipi' => 'Dolu-Dolu',
                    'miktar' => round($totals['d_d'], 2),
                ];
            }
            if (($totals['b_d'] ?? 0) > 0.001) {
                $faturaKalemleri[] = [
                    'material_key' => $matKey,
                    'material_label' => $label,
                    'tasima_tipi' => 'Boş-Dolu',
                    'miktar' => round($totals['b_d'], 2),
                ];
            }
        }

        return [
            'dates' => array_keys($pivotData),
            'materials' => $allMaterials,
            'rows' => $outRows,
            'totals_row' => [
                'material_totals' => $totalsMaterial,
                'material_counts' => $totalsMaterialCounts,
                'row_total' => $grandTotal,
                'row_total_count' => array_sum($totalsMaterialCounts),
                'boş_dolu' => $totalsBoşDolu,
                'dolu_dolu' => $totalsDoluDolu,
            ],
            'fatura_kalemleri' => $faturaKalemleri,
            'fatura_toplam' => $grandTotal,
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

        if ($klinkerQuantity <= 0.001) {
            $klinkerGri['values']['bos_dolu_malzeme_calculated'] = '--';
            $curuf['values']['bos_dolu_malzeme_calculated'] = $curufQuantity > 0.001 ? 'Curuf' : '--';
            $petrokok['values']['bos_dolu_malzeme_calculated'] = $petrokokQuantity > 0.001 ? 'Petrokok (MS)' : '--';

            return;
        }

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
        $petrokok['values']['bos_dolu_malzeme_calculated'] = $petrokokBosDolu > 0 ? 'Petrokok (MS)' : '--';
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

        if ($numericValue !== null && $numericValue >= 1000 && $numericValue < 2958466 && class_exists(\PhpOffice\PhpSpreadsheet\Shared\Date::class)) {
            $tz = new \DateTimeZone('Europe/Istanbul');
            $prev = \PhpOffice\PhpSpreadsheet\Shared\Date::getExcelCalendar();
            \PhpOffice\PhpSpreadsheet\Shared\Date::setExcelCalendar(\PhpOffice\PhpSpreadsheet\Shared\Date::CALENDAR_WINDOWS_1900);
            try {
                $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($numericValue, $tz);
                $year = (int) $dt->format('Y');
                $nowYear = (int) date('Y');
                if ($year > $nowYear + 1 || $year < $nowYear - 2) {
                    \PhpOffice\PhpSpreadsheet\Shared\Date::setExcelCalendar(\PhpOffice\PhpSpreadsheet\Shared\Date::CALENDAR_MAC_1904);
                    $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($numericValue, $tz);
                }

                return $dt->format('d.m.Y');
            } catch (\Throwable) {
            } finally {
                \PhpOffice\PhpSpreadsheet\Shared\Date::setExcelCalendar($prev);
            }
        }

        $formats = [
            'j.n.Y H:i:s',
            'j.n.Y H:i',
            'j.n.Y g:i:s A',
            'j.n.Y',
            'd.m.Y g:i:s A',
            'd.m.Y g:i A',
            'd.m.Y H:i',
            'd.m.Y H:i:s',
            'd.m.Y',
            'Y-m-d H:i:s',
            'Y-m-d',
            'n/j/Y',
            'm/d/Y',
            'j/n/Y',
            'd/m/Y',
        ];
        if (str_contains($value, '/')) {
            $formatsSlashFirst = ['n/j/Y', 'm/d/Y', 'n/j/Y H:i:s', 'm/d/Y H:i:s', 'j/n/Y', 'd/m/Y'];
            foreach ($formatsSlashFirst as $fmt) {
                $dt = @\DateTime::createFromFormat($fmt, $value);
                if ($dt !== false) {
                    return $dt->format('d.m.Y');
                }
            }
        }
        foreach ($formats as $fmt) {
            $dt = @\DateTime::createFromFormat($fmt, $value);
            if ($dt !== false) {
                return $dt->format('d.m.Y');
            }
        }

        if (preg_match('/^(\d{1,2})\.(\d{1,2})\.(\d{4})(?:\s|$)/', $value, $m)) {
            $d = (int) $m[1];
            $mo = (int) $m[2];
            $y = (int) $m[3];
            if ($d >= 1 && $d <= 31 && $mo >= 1 && $mo <= 12 && $y >= 1900 && $y <= 2100) {
                return sprintf('%02d.%02d.%04d', $d, $mo, $y);
            }
        }

        try {
            $parsed = \Carbon\Carbon::parse($value);
            if ($parsed->year >= 1900 && $parsed->year <= 2100) {
                return $parsed->format('d.m.Y');
            }
        } catch (\Throwable) {
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
