<?php

namespace App\Pricing\Services;

use App\Models\Order;
use App\Pricing\Models\PricingCondition;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;

class PricingService
{
    /**
     * Find the best applicable pricing condition for a shipment.
     * Prioritizes route-specific and vehicle-specific conditions over generic ones.
     *
     * @param  string|null  $origin  Çıkış noktası (şehir/bölge)
     * @param  string|null  $destination  Varış noktası (şehir/bölge)
     * @param  float  $weightKg  Toplam ağırlık (kg)
     * @param  float  $distanceKm  Toplam mesafe (km)
     * @param  string|null  $vehicleType  Araç tipi (truck, van, vb.)
     * @param  int  $companyId  Şirket ID
     * @return PricingCondition|null En uygun fiyat koşulu veya null
     */
    public function findApplicableCondition(
        ?string $origin,
        ?string $destination,
        float $weightKg = 0,
        float $distanceKm = 0,
        ?string $vehicleType = null,
        int $companyId = 0,
    ): ?PricingCondition {
        $now = Carbon::today();

        $query = PricingCondition::query()
            ->where('status', 1)
            ->where('company_id', $companyId)
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_from')->orWhere('valid_from', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('valid_to')->orWhere('valid_to', '>=', $now);
            });

        if ($vehicleType) {
            $query->where(function ($q) use ($vehicleType) {
                $q->whereNull('vehicle_type')->orWhere('vehicle_type', $vehicleType);
            });
        }

        if ($origin) {
            $query->where(function ($q) use ($origin) {
                $q->whereNull('route_origin')->orWhere('route_origin', $origin);
            });
        }

        if ($destination) {
            $query->where(function ($q) use ($destination) {
                $q->whereNull('route_destination')->orWhere('route_destination', $destination);
            });
        }

        // Route-specific conditions take priority over generic ones
        $conditions = $query->get()->sortByDesc(function (PricingCondition $c) use ($weightKg, $distanceKm) {
            $score = 0;
            if ($c->route_origin) {
                $score += 10;
            }
            if ($c->route_destination) {
                $score += 10;
            }
            if ($c->vehicle_type) {
                $score += 5;
            }

            // Check weight/distance ranges
            if ($c->condition_type === 'weight_based' && $weightKg > 0) {
                if ($c->weight_from !== null && $weightKg < (float) $c->weight_from) {
                    return -999;
                }
                if ($c->weight_to !== null && $weightKg > (float) $c->weight_to) {
                    return -999;
                }
                $score += 20;
            }

            if ($c->condition_type === 'distance_based' && $distanceKm > 0) {
                if ($c->distance_from !== null && $distanceKm < (float) $c->distance_from) {
                    return -999;
                }
                if ($c->distance_to !== null && $distanceKm > (float) $c->distance_to) {
                    return -999;
                }
                $score += 20;
            }

            return $score;
        });

        return $conditions->first();
    }

    /**
     * Calculate price from a condition given weight and distance.
     * Applies weight_based, distance_based, flat or zone_based pricing logic.
     * Returns the higher of calculated price or minimum charge.
     *
     * @param  PricingCondition  $condition  Fiyat koşulu
     * @param  float  $weightKg  Ağırlık (kg)
     * @param  float  $distanceKm  Mesafe (km)
     * @return float Hesaplanan fiyat (minimum ücret dahil)
     */
    public function calculatePrice(PricingCondition $condition, float $weightKg = 0, float $distanceKm = 0): float
    {
        $price = match ($condition->condition_type) {
            'weight_based' => $weightKg * (float) $condition->price_per_kg,
            'distance_based' => $distanceKm * (float) $condition->price_per_km,
            'flat' => (float) $condition->flat_rate,
            'zone_based' => (float) $condition->flat_rate,
            default => 0.0,
        };

        $minCharge = (float) ($condition->min_charge ?? 0);

        return max($price, $minCharge);
    }

    /**
     * Attempt to find and apply a pricing condition to an order.
     * Updates the order's freight_price and pricing_condition_id fields.
     *
     * @param  Order  $order  Fiyatlandırılacak sipariş
     * @return float|null Hesaplanan navlun fiyatı veya null (koşul bulunamazsa)
     */
    public function applyToOrder(Order $order): ?float
    {
        $condition = $this->findApplicableCondition(
            origin: $order->loading_city ?? null,
            destination: $order->delivery_city ?? null,
            weightKg: (float) ($order->weight ?? 0),
            distanceKm: (float) ($order->distance ?? 0),
            vehicleType: $order->vehicle_type ?? null,
            companyId: (int) $order->company_id,
        );

        if (! $condition) {
            return null;
        }

        $price = $this->calculatePrice(
            $condition,
            (float) ($order->weight ?? 0),
            (float) ($order->distance ?? 0),
        );

        $order->update([
            'freight_price' => $price,
            'pricing_condition_id' => $condition->id,
        ]);

        return $price;
    }

    /**
     * Get paginated conditions for a company.
     * Supports filtering by company_id, condition_type and status.
     *
     * @param  array<string, mixed>  $filters  Filtreler (company_id, condition_type, status)
     * @param  int  $perPage  Sayfa başına kayıt sayısı
     * @return LengthAwarePaginator Sayfalanmış fiyat koşulları listesi
     */
    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = PricingCondition::query()->with('company');

        if (isset($filters['company_id'])) {
            $query->where('company_id', $filters['company_id']);
        }

        if (isset($filters['condition_type'])) {
            $query->where('condition_type', $filters['condition_type']);
        }

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->latest()->paginate($perPage);
    }
}
