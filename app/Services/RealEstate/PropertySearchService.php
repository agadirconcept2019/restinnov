<?php

namespace App\Services\RealEstate;

use App\Models\RealEstate\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PropertySearchService
{
    public function paginated(array $filters = [], int $perPage = 12): LengthAwarePaginator
    {
        return Property::query()
            ->with([
                'translations',
                'type.translations',
                'mode.translations',
                'city.translations',
                'area.translations',
                'amenities.translations',
                'images.media',
            ])
            ->published()
            ->when($filters['type'] ?? null, fn ($q, $slug) => $q->whereHas('type', fn ($sub) => $sub->where('slug', $slug)))
            ->when($filters['mode'] ?? null, fn ($q, $slug) => $q->whereHas('mode', fn ($sub) => $sub->where('slug', $slug)))
            ->when($filters['city'] ?? null, fn ($q, $slug) => $q->whereHas('city', fn ($sub) => $sub->where('slug', $slug)))
            ->when($filters['area'] ?? null, fn ($q, $slug) => $q->whereHas('area', fn ($sub) => $sub->where('slug', $slug)))
            ->when($filters['guests'] ?? null, fn ($q, $guests) => $q->where('max_guests', '>=', (int) $guests))
            ->latest('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }
}
