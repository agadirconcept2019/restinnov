<?php

namespace App\Modules\RealEstate\Services;

use App\Models\RealEstate\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class PropertySearchServiceV2
{
    public function search(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        ksort($filters);
        $cacheKey = 'realestate:search:'.app()->getLocale().':'.md5(json_encode($filters));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($filters, $perPage) {
            $query = Property::query()
                ->with(['translations', 'type.translations', 'mode.translations', 'city.translations', 'area.translations', 'amenities.translations', 'images.media'])
                ->published()
                ->when($filters['city'] ?? null, fn ($q, $slug) => $q->whereHas('city', fn ($sq) => $sq->where('slug', $slug)))
                ->when($filters['area'] ?? null, fn ($q, $slug) => $q->whereHas('area', fn ($sq) => $sq->where('slug', $slug)))
                ->when($filters['type'] ?? null, fn ($q, $slug) => $q->whereHas('type', fn ($sq) => $sq->where('slug', $slug)))
                ->when($filters['mode'] ?? null, fn ($q, $slug) => $q->whereHas('mode', fn ($sq) => $sq->where('slug', $slug)))
                ->when($filters['guests'] ?? ($filters['guests_min'] ?? null), fn ($q, $guests) => $q->where('max_guests', '>=', (int) $guests))
                ->when($filters['price_min'] ?? null, fn ($q, $v) => $q->where('base_price_per_night', '>=', (float) $v))
                ->when($filters['price_max'] ?? null, fn ($q, $v) => $q->where('base_price_per_night', '<=', (float) $v));

            foreach ((array) ($filters['amenities'] ?? []) as $amenity) {
                $query->whereHas('amenities', fn ($aq) => $aq->where('slug', $amenity));
            }

            if (! empty($filters['checkin']) && ! empty($filters['checkout'])) {
                $query->whereDoesntHave('availabilities', function ($aq) use ($filters) {
                    $aq->whereBetween('date', [$filters['checkin'], date('Y-m-d', strtotime($filters['checkout'].' -1 day'))])
                        ->whereIn('status', ['booked', 'blocked', 'pending']);
                });
            }

            match ($filters['sort'] ?? 'newest') {
                'price_asc' => $query->orderBy('base_price_per_night'),
                'price_desc' => $query->orderByDesc('base_price_per_night'),
                'featured' => $query->orderByDesc('is_featured')->orderByDesc('published_at'),
                default => $query->orderByDesc('published_at'),
            };

            return $query->paginate($perPage)->withQueryString();
        });
    }
}
