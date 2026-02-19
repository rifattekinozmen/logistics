<?php

namespace App\Delivery\Services;

use App\Models\DeliveryImportBatch;
use DateTime;
use DateTimeZone;
use Throwable;

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

        $uyTanimIndex = 9;
        $adIndex = 22;

        /*
         * Petrokok rota tercihi: 'ekinciler' (varsayılan) veya 'isdemir'.
         * 'isdemir' seçildiğinde Petrokok, curuf_route grubuna dahil edilir.
         */
        $petrokokRoutePref = $batch->petrokok_route_preference ?? 'ekinciler';
        $petrokokRouteKey = $petrokokRoutePref === 'isdemir' ? 'curuf_route' : 'petrokok_route';

        $rows = $batch->reportRows()->orderBy('row_index')->get();
        $pivotData = [];
        /** @var array<string, array{uy_tanim: string, ad: string}> Malzeme key → ÜY Tanım & Ad bilgisi */
        $materialLocationInfo = [];

        foreach ($rows as $row) {
            $data = $row->row_data ?? [];
            $date = $this->normalizeDateForPivot((string) ($data[$dateIndex] ?? ''));
            $code = trim((string) ($data[$materialCodeIndex] ?? ''));
            $short = $materialShortIndex !== null ? trim((string) ($data[$materialShortIndex] ?? '')) : '';
            $matKey = ($code !== '' && $short !== '') ? $code.' | '.$short : ($code ?: $short ?: '-');

            /*
             * Tüm malzemeler ÜY Tanım (firma/tesis) bazında ayrıştırılır.
             * Aynı malzeme kodu farklı firmalardan gelebilir (örn. Klinker: Adana Fabrika / ÇİMSA,
             * Petrokok: SÜPER ENERJİ / BULK TRADING).
             * matKey'e firma bilgisi eklenerek pivot tablosunda ayrı sütunlar oluşturulur.
             */
            $uyTanim = trim((string) ($data[$uyTanimIndex] ?? ''));
            if ($uyTanim !== '') {
                $matKey .= ' ['.$uyTanim.']';
            }

            if ($date === '' || $matKey === '' || $matKey === '-') {
                continue;
            }

            $qty = $this->extractQuantity($data[$quantityIndex] ?? null);
            if ($qty === null) {
                continue;
            }

            if (! isset($materialLocationInfo[$matKey])) {
                $materialLocationInfo[$matKey] = [
                    'uy_tanim' => trim((string) ($data[$uyTanimIndex] ?? '')),
                    'ad' => trim((string) ($data[$adIndex] ?? '')),
                ];
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

            /** @var array<string, float> Klinker varyant key → miktar (firma/tesis bazlı) */
            $klinkerVariants = [];
            $klinkerQuantity = 0;
            /** @var array<string, float> Cüruf varyant key → miktar (firma bazlı) */
            $curufVariants = [];
            $curufQuantity = 0;
            /** @var array<string, float> Petrokok varyant key → miktar (firma bazlı) */
            $petrokokVariants = [];
            $petrokokQuantity = 0;
            foreach ($pivotData[$date] as $materialKey => $values) {
                $q = $values['quantity'] ?? 0;
                $upper = mb_strtoupper($materialKey);
                /*
                 * matKey formatı: "KOD | KISA METİN [ÜY TANIM]"
                 * Köşeli parantez içindeki ÜY Tanım kısmını ayırarak sadece malzeme kodu ve kısa metni kontrol ederiz.
                 */
                $bracketPos = strpos($upper, '[');
                $upperWithoutBracket = $bracketPos !== false ? substr($upper, 0, $bracketPos) : $upper;
                $parts = explode('|', $upperWithoutBracket);
                $materialCode = trim($parts[0] ?? '');
                $materialShort = trim($parts[1] ?? '');
                if (stripos($materialCode, 'KLINKER') !== false || stripos($materialShort, 'KLINKER') !== false) {
                    $klinkerVariants[$materialKey] = $q;
                    $klinkerQuantity += $q;
                } elseif (stripos($materialCode, 'CÜRUF') !== false || stripos($materialCode, 'CURUF') !== false || stripos($materialShort, 'CÜRUF') !== false || stripos($materialShort, 'CURUF') !== false) {
                    $curufVariants[$materialKey] = $q;
                    $curufQuantity += $q;
                } elseif (stripos($materialCode, 'PETROKOK') !== false || stripos($materialCode, 'P.KOK') !== false || stripos($materialShort, 'PETROKOK') !== false || stripos($materialShort, 'P.KOK') !== false) {
                    $petrokokVariants[$materialKey] = $q;
                    $petrokokQuantity += $q;
                }
            }

            $this->applyMaterialMatchingLogic($pivotData[$date], $satirToplami);

            /*
             * Ardışık D-D eşleşme mantığı (Cemiloglu):
             * Klinker önce Cüruf ile, kalan Klinker sonra Petrokok ile D-D yapar.
             * Böylece Klinker birden fazla partner ile eşleşebilir ve artığı minimize edilir.
             *
             * Adım 1: Klinker ↔ Cüruf D-D
             * Adım 2: Kalan Klinker ↔ Petrokok D-D
             * Artanlar B-D olarak sayılır.
             */
            $ddKlinkerCuruf = min($klinkerQuantity, $curufQuantity);
            $remainingKlinkerAfterCuruf = $klinkerQuantity - $ddKlinkerCuruf;
            $remainingCuruf = $curufQuantity - $ddKlinkerCuruf;

            $ddKlinkerPetrokok = min($remainingKlinkerAfterCuruf, $petrokokQuantity);
            $remainingKlinker = $remainingKlinkerAfterCuruf - $ddKlinkerPetrokok;
            $remainingPetrokok = $petrokokQuantity - $ddKlinkerPetrokok;

            $doluDoluSatir = 2 * ($ddKlinkerCuruf + $ddKlinkerPetrokok);

            /* B-D artanlar */
            $totalKlinkerBd = $remainingKlinker;
            $curufBd = $remainingCuruf;
            $petrokokBd = $remainingPetrokok;

            if ($klinkerQuantity <= 0.001) {
                $bosDoluSatir = $curufQuantity + $petrokokQuantity;
            } else {
                $bosDoluSatir = $totalKlinkerBd + $curufBd + $petrokokBd;
            }

            /* BOŞ-DOLU TAŞINAN MALZEME KISA METNİ belirleme */
            $satirBosDoluMalzeme = '--';
            $bdParts = [];
            if ($petrokokBd > 0.001) {
                $bdParts[] = 'Petrokok (MS)';
            }
            if ($curufBd > 0.001) {
                $bdParts[] = 'Curuf';
            }
            if ($totalKlinkerBd > 0.001) {
                $bdParts[] = 'Klinker';
            }
            if ($bdParts !== []) {
                $satirBosDoluMalzeme = implode('+', $bdParts);
            }

            /* Fallback: malzeme yoksa ağırlık tabanlı kontrol */
            if ($satirBosDoluMalzeme === '--' && $bosDoluSatir <= 0.001) {
                $rowDolu = 0;
                $rowFirma = 0;
                $rowGecerli2 = 0;
                foreach ($pivotData[$date] as $values) {
                    $rowDolu += $values['dolu_agirlik'] ?? 0;
                    $rowFirma += $values['firma_miktari'] ?? 0;
                    $rowGecerli2 += $values['gecerli_miktar_2'] ?? 0;
                }
                if (abs($rowDolu - ($rowFirma + $rowGecerli2)) >= 0.01) {
                    if ($rowDolu > ($rowFirma + $rowGecerli2)) {
                        $satirBosDoluMalzeme = 'Klinker(Gri)';
                    } elseif ($rowFirma < 0.01) {
                        $satirBosDoluMalzeme = 'Curuf';
                    } elseif ($satirToplami <= $rowFirma) {
                        $satirBosDoluMalzeme = 'Petrokok (MS)';
                    } else {
                        $satirBosDoluMalzeme = 'Petrokok (MS)+Curuf';
                    }
                }
            }

            foreach ($pivotData[$date] as $materialKey => $values) {
                $pivotData[$date][$materialKey]['bos_dolu_tasinan'] = $bosDoluSatir;
                $pivotData[$date][$materialKey]['dolu_dolu_tasinan'] = $doluDoluSatir;
                $pivotData[$date][$materialKey]['bos_dolu_malzeme'] = $satirBosDoluMalzeme;
            }

            /*
             * Fatura kalemlerini ROTA bazlı takip et (ardışık D-D mantığı ile).
             *
             * Adım 1 – Klinker ↔ Cüruf D-D → curuf_route
             * Adım 2 – Kalan Klinker ↔ Petrokok D-D → petrokok_route (veya isdemir seçildiyse curuf_route)
             * B-D: Klinker artanı → curuf_route, Cüruf artanı → curuf_route, Petrokok artanı → petrokok_route
             */

            /* Adım 1: Klinker ↔ Cüruf D-D */
            if ($ddKlinkerCuruf > 0.001) {
                $halfDdCuruf = $ddKlinkerCuruf;
                foreach ($klinkerVariants as $kKey => $kQty) {
                    $share = $klinkerQuantity > 0.001 ? $halfDdCuruf * ($kQty / $klinkerQuantity) : 0;
                    if ($share > 0.001) {
                        $faturaTotals['curuf_route'][$kKey] = $faturaTotals['curuf_route'][$kKey] ?? ['d_d' => 0, 'b_d' => 0];
                        $faturaTotals['curuf_route'][$kKey]['d_d'] += $share;
                    }
                }
                foreach ($curufVariants as $cKey => $cQty) {
                    $cShare = $curufQuantity > 0.001 ? $halfDdCuruf * ($cQty / $curufQuantity) : 0;
                    if ($cShare > 0.001) {
                        $faturaTotals['curuf_route'][$cKey] = $faturaTotals['curuf_route'][$cKey] ?? ['d_d' => 0, 'b_d' => 0];
                        $faturaTotals['curuf_route'][$cKey]['d_d'] += $cShare;
                    }
                }
            }

            /* Adım 2: Kalan Klinker ↔ Petrokok D-D */
            if ($ddKlinkerPetrokok > 0.001) {
                $halfDdPetrokok = $ddKlinkerPetrokok;
                foreach ($klinkerVariants as $kKey => $kQty) {
                    $share = $klinkerQuantity > 0.001 ? $halfDdPetrokok * ($kQty / $klinkerQuantity) : 0;
                    if ($share > 0.001) {
                        $faturaTotals[$petrokokRouteKey][$kKey] = $faturaTotals[$petrokokRouteKey][$kKey] ?? ['d_d' => 0, 'b_d' => 0];
                        $faturaTotals[$petrokokRouteKey][$kKey]['d_d'] += $share;
                    }
                }
                foreach ($petrokokVariants as $pKey => $pQty) {
                    $pShare = $petrokokQuantity > 0.001 ? $halfDdPetrokok * ($pQty / $petrokokQuantity) : 0;
                    if ($pShare > 0.001) {
                        $faturaTotals[$petrokokRouteKey][$pKey] = $faturaTotals[$petrokokRouteKey][$pKey] ?? ['d_d' => 0, 'b_d' => 0];
                        $faturaTotals[$petrokokRouteKey][$pKey]['d_d'] += $pShare;
                    }
                }
            }

            /* B-D: Klinker artanı → curuf_route */
            if ($totalKlinkerBd > 0.001) {
                foreach ($klinkerVariants as $kKey => $kQty) {
                    $share = $klinkerQuantity > 0.001 ? $totalKlinkerBd * ($kQty / $klinkerQuantity) : 0;
                    if ($share > 0.001) {
                        $faturaTotals['curuf_route'][$kKey] = $faturaTotals['curuf_route'][$kKey] ?? ['d_d' => 0, 'b_d' => 0];
                        $faturaTotals['curuf_route'][$kKey]['b_d'] += $share;
                    }
                }
            }
            /* B-D: Cüruf artanı → curuf_route */
            if ($curufBd > 0.001 && $curufVariants !== []) {
                foreach ($curufVariants as $cKey => $cQty) {
                    $cShare = $curufQuantity > 0.001 ? $curufBd * ($cQty / $curufQuantity) : 0;
                    if ($cShare > 0.001) {
                        $faturaTotals['curuf_route'][$cKey] = $faturaTotals['curuf_route'][$cKey] ?? ['d_d' => 0, 'b_d' => 0];
                        $faturaTotals['curuf_route'][$cKey]['b_d'] += $cShare;
                    }
                }
            }
            /* B-D: Petrokok artanı → petrokok_route */
            if ($petrokokBd > 0.001 && $petrokokVariants !== []) {
                foreach ($petrokokVariants as $pKey => $pQty) {
                    $pShare = $petrokokQuantity > 0.001 ? $petrokokBd * ($pQty / $petrokokQuantity) : 0;
                    if ($pShare > 0.001) {
                        $faturaTotals[$petrokokRouteKey][$pKey] = $faturaTotals[$petrokokRouteKey][$pKey] ?? ['d_d' => 0, 'b_d' => 0];
                        $faturaTotals[$petrokokRouteKey][$pKey]['b_d'] += $pShare;
                    }
                }
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

        /*
         * Rota etiketlerini ve yön bilgilerini oluştur.
         * curuf_route → İsdemir Tesisi  |  Klinker: Adana Fabrika → İskenderun 1, Cüruf: İskenderun 1 → Adana Fabrika
         * petrokok_route → Ekinciler Tesisi  |  Klinker: Adana Fabrika → Ekinciler Limanı, Petrokok: Ekinciler Limanı → Adana Fabrika
         */
        $klinkerInfo = null;
        foreach ($materialLocationInfo as $matKey => $info) {
            if (stripos(mb_strtoupper($matKey), 'KLINKER') !== false) {
                $klinkerInfo = $info;
                break;
            }
        }

        $baseFactory = $klinkerInfo['uy_tanim'] ?? 'Adana Fabrika';
        $klinkerDest = $klinkerInfo['ad'] ?? 'İskenderun 1';

        $isdemirLabel = (stripos($klinkerDest, 'skenderun') !== false || stripos($klinkerDest, 'İSDEMİR') !== false)
            ? 'İsdemir Tesisi'
            : ($klinkerDest ?: 'İsdemir Tesisi');

        $routeConfigs = [
            'curuf_route' => [
                'label' => $isdemirLabel,
                'klinker_dir' => $baseFactory.' → '.$klinkerDest,
                'partner_dir' => $klinkerDest.' → '.$baseFactory,
            ],
            'petrokok_route' => [
                'label' => 'Ekinciler Tesisi',
                'klinker_dir' => $baseFactory.' → Ekinciler Limanı',
                'partner_dir' => 'Ekinciler Limanı → '.$baseFactory,
            ],
        ];

        /*
         * Petrokok İsdemir'e yönlendirilmişse, curuf_route içindeki Petrokok malzemeleri için
         * özel yön bilgisi kullanılacak (İskenderun yönü, Ekinciler değil).
         */
        $petrokokInIsdemir = $petrokokRoutePref === 'isdemir';

        /*
         * Klinker/Cüruf/Petrokok dışındaki malzemeleri (ARM-0103 Uçucu Kül vb.) kendi rota grubu olarak ekle.
         * Bu malzemeler D-D/B-D eşleşmesine katılmaz; tamamı "Boş-Dolu" olarak taşınır.
         * materialLocationInfo'daki ÜY Tanım ve Ad bilgisi ile yön belirlenir.
         */
        $otherMaterialTotals = [];
        foreach ($totalsMaterial as $matKey => $totalQty) {
            if ($totalQty <= 0.001) {
                continue;
            }
            $upperKey = mb_strtoupper($matKey);
            $bracketPos = strpos($upperKey, '[');
            $upperClean = $bracketPos !== false ? substr($upperKey, 0, $bracketPos) : $upperKey;
            $isKnown = stripos($upperClean, 'KLINKER') !== false
                || stripos($upperClean, 'CÜRUF') !== false || stripos($upperClean, 'CURUF') !== false
                || stripos($upperClean, 'PETROKOK') !== false || stripos($upperClean, 'P.KOK') !== false;
            if (! $isKnown) {
                $otherMaterialTotals[$matKey] = $totalQty;
            }
        }
        foreach ($otherMaterialTotals as $matKey => $totalQty) {
            $info = $materialLocationInfo[$matKey] ?? [];
            $uyTanim = $info['uy_tanim'] ?? '';
            $ad = $info['ad'] ?? '';
            $routeKey = 'other_'.md5($matKey);

            /*
             * Yön: ÜY Tanım (çıkış) → Ad (varış) veya Ad → baseFactory.
             * Genelde bu malzemeler dışarıdan fabrikaya gelir.
             */
            $direction = $uyTanim !== '' && $ad !== ''
                ? $ad.' → '.$baseFactory
                : ($ad !== '' ? $ad.' → '.$baseFactory : '');

            $faturaTotals[$routeKey][$matKey] = ['d_d' => 0, 'b_d' => $totalQty];

            $routeConfigs[$routeKey] = [
                'label' => $uyTanim ?: ($ad ?: 'Diğer'),
                'klinker_dir' => $direction,
                'partner_dir' => $direction,
            ];
        }

        $faturaRotaGruplari = [];
        $faturaGenelToplam = 0;

        $allRouteKeys = array_unique(array_merge(['curuf_route', 'petrokok_route'], array_keys($routeConfigs)));
        foreach ($allRouteKeys as $routeKey) {
            $routeItems = $faturaTotals[$routeKey] ?? [];
            if ($routeItems === []) {
                continue;
            }
            if (! isset($routeConfigs[$routeKey])) {
                continue;
            }

            $cfg = $routeConfigs[$routeKey];
            $routeKalemleri = [];
            $routeToplam = 0;
            foreach ($routeItems as $matKey => $totals) {
                $codeParts = explode(' | ', $matKey, 2);
                $materialCode = trim($codeParts[0] ?? '');
                $materialShort = trim($codeParts[1] ?? $materialCode);
                $isKlinker = stripos($matKey, 'KLINKER') !== false;
                $isPetrokok = stripos($matKey, 'PETROKOK') !== false || stripos($matKey, 'P.KOK') !== false;

                if ($isKlinker) {
                    $direction = $cfg['klinker_dir'];
                } elseif ($isPetrokok && $petrokokInIsdemir && $routeKey === 'curuf_route') {
                    // Petrokok İsdemir grubunda: İskenderun yönü yerine gerçek Petrokok yönü
                    $direction = $klinkerDest.' → '.$baseFactory;
                } else {
                    $direction = $cfg['partner_dir'];
                }

                if (($totals['d_d'] ?? 0) > 0.001) {
                    $amount = round($totals['d_d'], 2);
                    $routeKalemleri[] = [
                        'material_key' => $matKey,
                        'material_code' => $materialCode,
                        'material_short' => $materialShort,
                        'nerden_nereye' => $direction,
                        'tasima_tipi' => 'Dolu-Dolu',
                        'miktar' => $amount,
                    ];
                    $routeToplam += $amount;
                }
                if (($totals['b_d'] ?? 0) > 0.001) {
                    $amount = round($totals['b_d'], 2);
                    $routeKalemleri[] = [
                        'material_key' => $matKey,
                        'material_code' => $materialCode,
                        'material_short' => $materialShort,
                        'nerden_nereye' => $direction,
                        'tasima_tipi' => 'Boş-Dolu',
                        'miktar' => $amount,
                    ];
                    $routeToplam += $amount;
                }
            }

            /* Sıralama: Klinker → Cüruf → Petrokok → diğer. Aynı malzeme için D-D önce, B-D sonra. */
            usort($routeKalemleri, function (array $a, array $b): int {
                $groupOrder = function (string $key): int {
                    $u = mb_strtoupper($key);
                    if (stripos($u, 'KLINKER') !== false) {
                        return 0;
                    }
                    if (stripos($u, 'CÜRUF') !== false || stripos($u, 'CURUF') !== false) {
                        return 1;
                    }
                    if (stripos($u, 'PETROKOK') !== false || stripos($u, 'P.KOK') !== false) {
                        return 2;
                    }

                    return 3;
                };
                $cmpGroup = $groupOrder($a['material_key']) <=> $groupOrder($b['material_key']);
                if ($cmpGroup !== 0) {
                    return $cmpGroup;
                }
                $cmpKey = $a['material_key'] <=> $b['material_key'];
                if ($cmpKey !== 0) {
                    return $cmpKey;
                }
                $tipOrder = ['Dolu-Dolu' => 0, 'Boş-Dolu' => 1];

                return ($tipOrder[$a['tasima_tipi']] ?? 2) <=> ($tipOrder[$b['tasima_tipi']] ?? 2);
            });

            if ($routeKalemleri !== []) {
                $faturaRotaGruplari[] = [
                    'route_key' => $routeKey,
                    'route_label' => $cfg['label'],
                    'kalemler' => $routeKalemleri,
                    'route_toplam' => round($routeToplam, 2),
                ];
                $faturaGenelToplam += $routeToplam;
            }
        }

        /*
         * Firma bazlı fatura tabloları.
         * row_data'daki FİRMA (index 38) alanına göre satırları gruplandırır.
         * Grup 1 (BRC): firma_adi = "BRC"
         * Grup 2 (Diğer): firma_adi = "A.Ş.", "GÜNEY", "TAŞERON" vb.
         */
        $firmaFaturaGruplari = $this->buildFirmaBasedInvoiceTables($batch, $config, $faturaRotaGruplari);

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
            'fatura_rota_gruplari' => $faturaRotaGruplari,
            'fatura_toplam' => round($faturaGenelToplam, 2),
            'firma_fatura_gruplari' => $firmaFaturaGruplari,
        ];
    }

    /**
     * FİRMA (firma_adı) bazlı fatura tabloları üretir.
     * Her firma grubu kendi satırlarıyla bağımsız D-D/B-D eşleştirme yapar.
     * Toplam malzeme miktarları firma bazında korunur.
     *
     * Grup 1 (BRC): firma_adi = "BRC"
     * Grup 2 (Diğer): firma_adi = "A.Ş.", "GÜNEY", "TAŞERON" vb.
     *
     * @param  array<int, array>  $faturaRotaGruplari  1. tablonun rota grupları (routeConfigs almak için)
     * @return array<int, array{label: string, rota_gruplari: array, toplam: float}>
     */
    protected function buildFirmaBasedInvoiceTables(DeliveryImportBatch $batch, array $config, array $faturaRotaGruplari = []): array
    {
        $mp = $config['material_pivot'] ?? null;
        if (! $mp || ! isset($mp['material_code_index'], $mp['quantity_index'])) {
            return [];
        }

        $mapping = $config['invoice_line_mapping'] ?? [];
        $firmaIndex = $mapping['firma'] ?? null;
        if ($firmaIndex === null) {
            return [];
        }

        $dateIndex = $this->resolveDateColumnIndex($batch, $mp);
        $materialCodeIndex = (int) $mp['material_code_index'];
        $materialShortIndex = isset($mp['material_short_text_index']) ? (int) $mp['material_short_text_index'] : null;
        $quantityIndex = (int) $mp['quantity_index'];
        $uyTanimIndex = 9;
        $adIndex = 22;

        $petrokokRoutePref = $batch->petrokok_route_preference ?? 'ekinciler';
        $petrokokRouteKey = $petrokokRoutePref === 'isdemir' ? 'curuf_route' : 'petrokok_route';

        $rows = $batch->reportRows()->orderBy('row_index')->get();

        /*
         * Satırları firma bazında grupla.
         */
        $firmaGroupDefs = [
            ['label' => 'BRC', 'match' => fn (string $f): bool => mb_strtoupper(trim($f)) === 'BRC', 'rows' => []],
            ['label' => 'A.Ş. / Güney / Taşeron', 'match' => fn (string $f): bool => mb_strtoupper(trim($f)) !== 'BRC' && trim($f) !== '', 'rows' => []],
        ];

        foreach ($rows as $row) {
            $data = $row->row_data ?? [];
            $firmaAdi = trim((string) ($data[$firmaIndex] ?? ''));
            if ($firmaAdi === '') {
                continue;
            }
            foreach ($firmaGroupDefs as &$gDef) {
                if (($gDef['match'])($firmaAdi)) {
                    $gDef['rows'][] = $row;
                    break;
                }
            }
            unset($gDef);
        }

        /* Rota config: Klinker bilgisini tüm satırlardan al */
        $klinkerInfo = null;
        foreach ($rows as $row) {
            $data = $row->row_data ?? [];
            $code = trim((string) ($data[$materialCodeIndex] ?? ''));
            $short = $materialShortIndex !== null ? trim((string) ($data[$materialShortIndex] ?? '')) : '';
            if (stripos($code, 'KLINKER') !== false || stripos($short, 'KLINKER') !== false) {
                $klinkerInfo = [
                    'uy_tanim' => trim((string) ($data[$uyTanimIndex] ?? '')),
                    'ad' => trim((string) ($data[$adIndex] ?? '')),
                ];
                break;
            }
        }
        $baseFactory = $klinkerInfo['uy_tanim'] ?? 'Adana Fabrika';
        $klinkerDest = $klinkerInfo['ad'] ?? 'İskenderun 1';

        $isdemirLabel = (stripos($klinkerDest, 'skenderun') !== false || stripos($klinkerDest, 'İSDEMİR') !== false)
            ? 'İsdemir Tesisi'
            : ($klinkerDest ?: 'İsdemir Tesisi');

        $routeConfigs = [
            'curuf_route' => [
                'label' => $isdemirLabel,
                'klinker_dir' => $baseFactory.' → '.$klinkerDest,
                'partner_dir' => $klinkerDest.' → '.$baseFactory,
            ],
            'petrokok_route' => [
                'label' => 'Ekinciler Tesisi',
                'klinker_dir' => $baseFactory.' → Ekinciler Limanı',
                'partner_dir' => 'Ekinciler Limanı → '.$baseFactory,
            ],
        ];

        $petrokokInIsdemir = $petrokokRoutePref === 'isdemir';

        $result = [];

        foreach ($firmaGroupDefs as $gDef) {
            if ($gDef['rows'] === []) {
                continue;
            }

            /*
             * Firma grubunun toplam malzeme miktarlarını hesapla.
             */
            $totalsMaterial = [];
            $materialLocationInfo = [];

            foreach ($gDef['rows'] as $row) {
                $data = $row->row_data ?? [];
                $code = trim((string) ($data[$materialCodeIndex] ?? ''));
                $short = $materialShortIndex !== null ? trim((string) ($data[$materialShortIndex] ?? '')) : '';
                $matKey = ($code !== '' && $short !== '') ? $code.' | '.$short : ($code ?: $short ?: '-');

                $uyTanim = trim((string) ($data[$uyTanimIndex] ?? ''));
                if ($uyTanim !== '') {
                    $matKey .= ' ['.$uyTanim.']';
                }

                if ($matKey === '' || $matKey === '-') {
                    continue;
                }

                $qty = $this->extractQuantity($data[$quantityIndex] ?? null);
                if ($qty === null) {
                    continue;
                }

                $totalsMaterial[$matKey] = ($totalsMaterial[$matKey] ?? 0) + $qty;

                if (! isset($materialLocationInfo[$matKey])) {
                    $materialLocationInfo[$matKey] = [
                        'uy_tanim' => $uyTanim,
                        'ad' => trim((string) ($data[$adIndex] ?? '')),
                    ];
                }
            }

            /*
             * Tarih bazlı pivot: her gün için Klinker ↔ Cüruf eşleşmesi yapılır.
             * Böylece İsdemir Klinker DD doğru hesaplanır (sadece Cüruf olan günlerde).
             */
            $pivotData = [];
            foreach ($gDef['rows'] as $row) {
                $data = $row->row_data ?? [];
                $date = $this->normalizeDateForPivot((string) ($data[$dateIndex] ?? ''));
                $code = trim((string) ($data[$materialCodeIndex] ?? ''));
                $short = $materialShortIndex !== null ? trim((string) ($data[$materialShortIndex] ?? '')) : '';
                $matKey = ($code !== '' && $short !== '') ? $code.' | '.$short : ($code ?: $short ?: '-');
                $uyTanim = trim((string) ($data[$uyTanimIndex] ?? ''));
                if ($uyTanim !== '') {
                    $matKey .= ' ['.$uyTanim.']';
                }
                if ($date === '' || $matKey === '' || $matKey === '-') {
                    continue;
                }
                $qty = $this->extractQuantity($data[$quantityIndex] ?? null);
                if ($qty === null) {
                    continue;
                }
                $pivotData[$date][$matKey] = ($pivotData[$date][$matKey] ?? 0) + $qty;
            }

            /*
             * Malzeme gruplarını belirle.
             */
            $klinkerVariants = [];
            $curufVariants = [];
            $petrokokVariants = [];
            $klinkerQty = 0;
            $curufQty = 0;
            $petrokokQty = 0;

            foreach ($totalsMaterial as $matKey => $qty) {
                $upper = mb_strtoupper($matKey);
                $bracketPos = strpos($upper, '[');
                $upperClean = $bracketPos !== false ? substr($upper, 0, $bracketPos) : $upper;

                if (stripos($upperClean, 'KLINKER') !== false) {
                    $klinkerVariants[$matKey] = $qty;
                    $klinkerQty += $qty;
                } elseif (stripos($upperClean, 'CÜRUF') !== false || stripos($upperClean, 'CURUF') !== false) {
                    $curufVariants[$matKey] = $qty;
                    $curufQty += $qty;
                } elseif (stripos($upperClean, 'PETROKOK') !== false || stripos($upperClean, 'P.KOK') !== false) {
                    $petrokokVariants[$matKey] = $qty;
                    $petrokokQty += $qty;
                }
            }

            /*
             * Tarih bazlı D-D/B-D eşleştirme:
             * 1) Klinker ↔ Cüruf DD (İsdemir rotası) → tarih bazlı
             * 2) Kalan Klinker → Ekinciler DD (Klinker hiçbir zaman BD olmaz)
             * 3) Petrokok DD = Ekinciler Klinker DD miktarı kadar
             *    → BULK/SÜPER oranı tarih bazlı: Klinker artanı bir sonraki güne aktarılarak
             *      Petrokok ile sıralı eşleştirme yapılır
             * 4) Petrokok BD = kalan Petrokok
             */
            $faturaTotals = [];

            $ddKlinkerCurufTotal = 0;
            $remainingCurufTotal = 0;
            $klinkerEkincilerDDTotal = 0;

            /** @var array<string, array{d_d: float, b_d: float}> matKey → [d_d, b_d] */
            $petrokokVarDDBD = [];

            /*
             * Tarihleri sırala ve her gün için:
             * - Klinker ↔ Cüruf DD (İsdemir)
             * - Kalan Klinker → Ekinciler DD (carry-over ile)
             * - Kalan Klinker kadar Petrokok DD (o günkü BULK/SÜPER oranında)
             * - Artanlar bir sonraki güne taşınır
             */
            $sortedDates = array_keys($pivotData);
            sort($sortedDates);

            $carryOverKlinker = 0;

            foreach ($sortedDates as $date) {
                $materials = $pivotData[$date];
                $dayKlinker = 0;
                $dayCuruf = 0;
                $dayPetrokokTotal = 0;
                $dayPetrokokVars = [];

                foreach ($materials as $matKey => $qty) {
                    $upper = mb_strtoupper($matKey);
                    $bracketPos = strpos($upper, '[');
                    $upperClean = $bracketPos !== false ? substr($upper, 0, $bracketPos) : $upper;
                    if (stripos($upperClean, 'KLINKER') !== false) {
                        $dayKlinker += $qty;
                    } elseif (stripos($upperClean, 'CÜRUF') !== false || stripos($upperClean, 'CURUF') !== false) {
                        $dayCuruf += $qty;
                    } elseif (stripos($upperClean, 'PETROKOK') !== false || stripos($upperClean, 'P.KOK') !== false) {
                        $dayPetrokokVars[$matKey] = ($dayPetrokokVars[$matKey] ?? 0) + $qty;
                        $dayPetrokokTotal += $qty;
                    }
                }

                /* Klinker ↔ Cüruf DD (İsdemir) */
                $dayDDKC = min($dayKlinker, $dayCuruf);
                $ddKlinkerCurufTotal += $dayDDKC;
                $remainingCurufTotal += ($dayCuruf - $dayDDKC);

                /* Kalan Klinker → Ekinciler DD (önceki günden taşınan dahil) */
                $dayKlinkerEkinciler = ($dayKlinker - $dayDDKC) + $carryOverKlinker;
                $klinkerEkincilerDDTotal += ($dayKlinker - $dayDDKC);

                /* Klinker Ekinciler ile Petrokok eşleştirme */
                $dayPetrokokDD = min($dayKlinkerEkinciler, $dayPetrokokTotal);
                $dayPetrokokBD = $dayPetrokokTotal - $dayPetrokokDD;

                /* Klinker artanı sonraki güne aktar */
                $carryOverKlinker = $dayKlinkerEkinciler - $dayPetrokokDD;
                if ($carryOverKlinker < 0.001) {
                    $carryOverKlinker = 0;
                }

                /* Petrokok DD/BD'yi o günün BULK/SÜPER oranında dağıt */
                foreach ($dayPetrokokVars as $pKey => $pQty) {
                    $ratio = $dayPetrokokTotal > 0.001 ? $pQty / $dayPetrokokTotal : 0;
                    $pDD = $dayPetrokokDD * $ratio;
                    $pBD = $dayPetrokokBD * $ratio;
                    $petrokokVarDDBD[$pKey] = $petrokokVarDDBD[$pKey] ?? ['d_d' => 0, 'b_d' => 0];
                    $petrokokVarDDBD[$pKey]['d_d'] += $pDD;
                    $petrokokVarDDBD[$pKey]['b_d'] += $pBD;
                }
            }

            /* İsdemir DD: Klinker ↔ Cüruf */
            if ($ddKlinkerCurufTotal > 0.001) {
                foreach ($klinkerVariants as $kKey => $kQty) {
                    $share = $klinkerQty > 0.001 ? $ddKlinkerCurufTotal * ($kQty / $klinkerQty) : 0;
                    if ($share > 0.001) {
                        $faturaTotals['curuf_route'][$kKey] = $faturaTotals['curuf_route'][$kKey] ?? ['d_d' => 0, 'b_d' => 0];
                        $faturaTotals['curuf_route'][$kKey]['d_d'] += $share;
                    }
                }
                foreach ($curufVariants as $cKey => $cQty) {
                    $cShare = $curufQty > 0.001 ? $ddKlinkerCurufTotal * ($cQty / $curufQty) : 0;
                    if ($cShare > 0.001) {
                        $faturaTotals['curuf_route'][$cKey] = $faturaTotals['curuf_route'][$cKey] ?? ['d_d' => 0, 'b_d' => 0];
                        $faturaTotals['curuf_route'][$cKey]['d_d'] += $cShare;
                    }
                }
            }

            /* Cüruf B-D artanı → curuf_route */
            if ($remainingCurufTotal > 0.001) {
                foreach ($curufVariants as $cKey => $cQty) {
                    $cShare = $curufQty > 0.001 ? $remainingCurufTotal * ($cQty / $curufQty) : 0;
                    if ($cShare > 0.001) {
                        $faturaTotals['curuf_route'][$cKey] = $faturaTotals['curuf_route'][$cKey] ?? ['d_d' => 0, 'b_d' => 0];
                        $faturaTotals['curuf_route'][$cKey]['b_d'] += $cShare;
                    }
                }
            }

            /* Klinker DD → petrokok_route (Ekinciler) */
            if ($klinkerEkincilerDDTotal > 0.001) {
                foreach ($klinkerVariants as $kKey => $kQty) {
                    $share = $klinkerQty > 0.001 ? $klinkerEkincilerDDTotal * ($kQty / $klinkerQty) : 0;
                    if ($share > 0.001) {
                        $faturaTotals[$petrokokRouteKey][$kKey] = $faturaTotals[$petrokokRouteKey][$kKey] ?? ['d_d' => 0, 'b_d' => 0];
                        $faturaTotals[$petrokokRouteKey][$kKey]['d_d'] += $share;
                    }
                }
            }

            /* Petrokok DD/BD → petrokok_route (tarih bazlı varyant carry-over dağılımı) */
            foreach ($petrokokVarDDBD as $pKey => $totals) {
                if (($totals['d_d'] ?? 0) > 0.001) {
                    $faturaTotals[$petrokokRouteKey][$pKey] = $faturaTotals[$petrokokRouteKey][$pKey] ?? ['d_d' => 0, 'b_d' => 0];
                    $faturaTotals[$petrokokRouteKey][$pKey]['d_d'] += $totals['d_d'];
                }
                if (($totals['b_d'] ?? 0) > 0.001) {
                    $faturaTotals[$petrokokRouteKey][$pKey] = $faturaTotals[$petrokokRouteKey][$pKey] ?? ['d_d' => 0, 'b_d' => 0];
                    $faturaTotals[$petrokokRouteKey][$pKey]['b_d'] += $totals['b_d'];
                }
            }

            /* Klinker/Cüruf/Petrokok dışındaki malzemeler (ARM-0103 vb.) */
            $localRouteConfigs = $routeConfigs;
            foreach ($totalsMaterial as $matKey => $totalQty) {
                if ($totalQty <= 0.001) {
                    continue;
                }
                $upperKey = mb_strtoupper($matKey);
                $bracketPos = strpos($upperKey, '[');
                $upperClean = $bracketPos !== false ? substr($upperKey, 0, $bracketPos) : $upperKey;
                $isKnown = stripos($upperClean, 'KLINKER') !== false
                    || stripos($upperClean, 'CÜRUF') !== false || stripos($upperClean, 'CURUF') !== false
                    || stripos($upperClean, 'PETROKOK') !== false || stripos($upperClean, 'P.KOK') !== false;
                if (! $isKnown) {
                    $info = $materialLocationInfo[$matKey] ?? [];
                    $uyTanim = $info['uy_tanim'] ?? '';
                    $ad = $info['ad'] ?? '';
                    $rKey = 'other_'.md5($matKey);
                    $direction = ($uyTanim !== '' && $ad !== '') ? $ad.' → '.$baseFactory : ($ad !== '' ? $ad.' → '.$baseFactory : '');
                    $faturaTotals[$rKey][$matKey] = ['d_d' => 0, 'b_d' => $totalQty];
                    $localRouteConfigs[$rKey] = [
                        'label' => $uyTanim ?: ($ad ?: 'Diğer'),
                        'klinker_dir' => $direction,
                        'partner_dir' => $direction,
                    ];
                }
            }

            /* Rota gruplarını oluştur */
            $faturaRotaGruplariLocal = [];
            $faturaGenelToplam = 0;

            $allRouteKeys = array_unique(array_merge(['curuf_route', 'petrokok_route'], array_keys($localRouteConfigs)));
            foreach ($allRouteKeys as $routeKey) {
                $routeItems = $faturaTotals[$routeKey] ?? [];
                if ($routeItems === [] || ! isset($localRouteConfigs[$routeKey])) {
                    continue;
                }
                $cfg = $localRouteConfigs[$routeKey];
                $routeKalemleri = [];
                $routeToplam = 0;

                foreach ($routeItems as $matKey => $totals) {
                    $codeParts = explode(' | ', $matKey, 2);
                    $materialCode = trim($codeParts[0] ?? '');
                    $materialShort = trim($codeParts[1] ?? $materialCode);
                    $isKlinker = stripos($matKey, 'KLINKER') !== false;
                    $isPetrokok = stripos($matKey, 'PETROKOK') !== false || stripos($matKey, 'P.KOK') !== false;

                    $direction = $isKlinker ? $cfg['klinker_dir']
                        : (($isPetrokok && $petrokokInIsdemir && $routeKey === 'curuf_route') ? $klinkerDest.' → '.$baseFactory : $cfg['partner_dir']);

                    if (($totals['d_d'] ?? 0) > 0.001) {
                        $amount = round($totals['d_d'], 2);
                        $routeKalemleri[] = ['material_key' => $matKey, 'material_code' => $materialCode, 'material_short' => $materialShort, 'nerden_nereye' => $direction, 'tasima_tipi' => 'Dolu-Dolu', 'miktar' => $amount];
                        $routeToplam += $amount;
                    }
                    if (($totals['b_d'] ?? 0) > 0.001) {
                        $amount = round($totals['b_d'], 2);
                        $routeKalemleri[] = ['material_key' => $matKey, 'material_code' => $materialCode, 'material_short' => $materialShort, 'nerden_nereye' => $direction, 'tasima_tipi' => 'Boş-Dolu', 'miktar' => $amount];
                        $routeToplam += $amount;
                    }
                }

                usort($routeKalemleri, function (array $a, array $b): int {
                    $go = fn (string $k): int => stripos(mb_strtoupper($k), 'KLINKER') !== false ? 0 : (stripos(mb_strtoupper($k), 'CÜRUF') !== false || stripos(mb_strtoupper($k), 'CURUF') !== false ? 1 : (stripos(mb_strtoupper($k), 'PETROKOK') !== false || stripos(mb_strtoupper($k), 'P.KOK') !== false ? 2 : 3));
                    $c = $go($a['material_key']) <=> $go($b['material_key']);
                    if ($c !== 0) {
                        return $c;
                    }
                    $c = $a['material_key'] <=> $b['material_key'];

                    return $c !== 0 ? $c : ((['Dolu-Dolu' => 0, 'Boş-Dolu' => 1][$a['tasima_tipi']] ?? 2) <=> (['Dolu-Dolu' => 0, 'Boş-Dolu' => 1][$b['tasima_tipi']] ?? 2));
                });

                if ($routeKalemleri !== []) {
                    $faturaRotaGruplariLocal[] = ['route_key' => $routeKey, 'route_label' => $cfg['label'], 'kalemler' => $routeKalemleri, 'route_toplam' => round($routeToplam, 2)];
                    $faturaGenelToplam += $routeToplam;
                }
            }

            if ($faturaRotaGruplariLocal !== []) {
                $result[] = ['label' => $gDef['label'], 'rota_gruplari' => $faturaRotaGruplariLocal, 'toplam' => round($faturaGenelToplam, 2)];
            }
        }

        return $result;
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
     * Cemiloglu sırası: Klinker → Cüruf → Petrokok(lar).
     * Petrokok birden fazla firma varyantına sahip olabilir; hepsi Cüruf'ten sonra sıralanır.
     *
     * @param  array<int, array{key: string, label: string}>  $materials
     * @return array<int, array{key: string, label: string}>
     */
    protected function reorderMaterialsCemilogluStyle(array $materials): array
    {
        usort($materials, function (array $a, array $b): int {
            $groupOrder = function (string $key): int {
                $upper = mb_strtoupper($key);
                $bracketPos = strpos($upper, '[');
                $upperClean = $bracketPos !== false ? substr($upper, 0, $bracketPos) : $upper;
                if (stripos($upperClean, 'KLINKER') !== false) {
                    return 0;
                }
                if (stripos($upperClean, 'CÜRUF') !== false || stripos($upperClean, 'CURUF') !== false) {
                    return 1;
                }
                if (stripos($upperClean, 'PETROKOK') !== false || stripos($upperClean, 'P.KOK') !== false) {
                    return 2;
                }

                return 3;
            };

            $cmp = $groupOrder($a['key']) <=> $groupOrder($b['key']);
            if ($cmp !== 0) {
                return $cmp;
            }

            return $a['key'] <=> $b['key'];
        });

        return $materials;
    }

    /**
     * Klinker (Gri) - CÜRUF - Petrokok(MS) eşleştirme mantığı (Cemiloglu).
     * Tüm malzeme grupları birden fazla firma bazlı varyanta sahip olabilir.
     *
     * @param  array<string, array>  $materials
     */
    protected function applyMaterialMatchingLogic(array &$materials, float $satirToplami): void
    {
        /** @var array<int, array{key: string, values: array}> Tüm Klinker varyantları (firma/tesis bazlı) */
        $klinkerRefs = [];
        /** @var array<int, array{key: string, values: array}> Tüm Cüruf varyantları (firma bazlı) */
        $curufRefs = [];
        /** @var array<int, array{key: string, values: array}> Tüm Petrokok varyantları (firma bazlı) */
        $petrokokRefs = [];

        foreach ($materials as $materialKey => $values) {
            $upper = mb_strtoupper($materialKey);
            $bracketPos = strpos($upper, '[');
            $upperWithoutBracket = $bracketPos !== false ? substr($upper, 0, $bracketPos) : $upper;
            $parts = explode('|', $upperWithoutBracket);
            $materialCode = trim($parts[0] ?? '');
            $materialShort = trim($parts[1] ?? '');

            if (stripos($materialCode, 'KLINKER') !== false || stripos($materialShort, 'KLINKER') !== false) {
                $klinkerRefs[] = ['key' => $materialKey, 'values' => &$materials[$materialKey]];
            } elseif (stripos($materialCode, 'CÜRUF') !== false || stripos($materialCode, 'CURUF') !== false || stripos($materialShort, 'CÜRUF') !== false || stripos($materialShort, 'CURUF') !== false) {
                $curufRefs[] = ['key' => $materialKey, 'values' => &$materials[$materialKey]];
            } elseif (stripos($materialCode, 'PETROKOK') !== false || stripos($materialCode, 'P.KOK') !== false || stripos($materialShort, 'PETROKOK') !== false || stripos($materialShort, 'P.KOK') !== false) {
                $petrokokRefs[] = ['key' => $materialKey, 'values' => &$materials[$materialKey]];
            }
        }

        $totalKlinkerQty = 0;
        foreach ($klinkerRefs as &$ref) {
            $totalKlinkerQty += $ref['values']['quantity'] ?? 0;
        }
        unset($ref);

        $curufQuantity = 0;
        foreach ($curufRefs as &$ref) {
            $curufQuantity += $ref['values']['quantity'] ?? 0;
        }
        unset($ref);

        $petrokokQuantity = 0;
        foreach ($petrokokRefs as &$ref) {
            $petrokokQuantity += $ref['values']['quantity'] ?? 0;
        }
        unset($ref);

        if ($klinkerRefs === [] && $curufRefs === [] && $petrokokRefs === []) {
            return;
        }

        /*
         * Ardışık D-D eşleşme: Klinker önce Cüruf ile, kalan Klinker sonra Petrokok ile.
         * Artanlar B-D olarak işaretlenir.
         */
        $ddKlinkerCuruf = min($totalKlinkerQty, $curufQuantity);
        $remainingKlinker = $totalKlinkerQty - $ddKlinkerCuruf;
        $remainingCuruf = $curufQuantity - $ddKlinkerCuruf;

        $ddKlinkerPetrokok = min($remainingKlinker, $petrokokQuantity);
        $klinkerBosDolu = $remainingKlinker - $ddKlinkerPetrokok;
        $curufBosDolu = $remainingCuruf;
        $petrokokBosDolu = $petrokokQuantity - $ddKlinkerPetrokok;

        foreach ($klinkerRefs as &$ref) {
            $ref['values']['bos_dolu_malzeme_calculated'] = $klinkerBosDolu > 0.001 ? 'Klinker' : '--';
        }
        unset($ref);
        foreach ($curufRefs as &$ref) {
            $ref['values']['bos_dolu_malzeme_calculated'] = $curufBosDolu > 0.001 ? 'Curuf' : '--';
        }
        unset($ref);
        foreach ($petrokokRefs as &$ref) {
            $ref['values']['bos_dolu_malzeme_calculated'] = $petrokokBosDolu > 0.001 ? 'Petrokok (MS)' : '--';
        }
        unset($ref);
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
            $tz = new DateTimeZone('Europe/Istanbul');
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
            } catch (Throwable) {
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
                $dt = @DateTime::createFromFormat($fmt, $value);
                if ($dt !== false) {
                    return $dt->format('d.m.Y');
                }
            }
        }
        foreach ($formats as $fmt) {
            $dt = @DateTime::createFromFormat($fmt, $value);
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
        } catch (Throwable) {
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
            $dtA = DateTime::createFromFormat('d.m.Y', $a);
            $dtB = DateTime::createFromFormat('d.m.Y', $b);
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
