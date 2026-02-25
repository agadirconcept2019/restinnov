<?php

namespace App\Modules\RealEstate\Services;

use App\Core\Cache\CacheVersionManager;
use App\Models\RealEstate\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class PropertySearchServiceV2
{
    public function __construct(private readonly CacheVersionManager $cacheVersionManager)
    {
    }

    public function search(array $filters, int $perPage = 12): LengthAwarePaginator
    {
        ksort($filters);
        $cacheKey = $this->cacheVersionManager->versionedKey(
            'realestate.search',
            app()->getLocale(),
            md5(json_encode($filters)),
        );

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
                $checkin = (string) $filters['checkin'];
                $checkoutMinusOne = date('Y-m-d', strtotime($filters['checkout'].' -1 day'));

                $query->whereNotExists(function ($sub) use ($checkin, $checkoutMinusOne) {
                    $sub->select(DB::raw(1))
                        ->from('property_availabilities as pa')
                        ->whereColumn('pa.property_id', 'properties.id')
                        ->whereBetween('pa.date', [$checkin, $checkoutMinusOne])
                        ->whereIn('pa.status', ['booked', 'blocked', 'pending']);
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

    public function featuredProperties(int $limit = 6)
    {
        $cacheKey = $this->cacheVersionManager->versionedKey('realestate.featured', app()->getLocale(), (string) $limit);

        return Cache::remember($cacheKey, now()->addMinutes(10), fn () => Property::query()
            ->with(['translations', 'city.translations'])
            ->published()
            ->where('is_featured', true)
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get());
    }
}
