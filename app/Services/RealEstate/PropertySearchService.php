<?php

namespace App\Services\RealEstate;

use App\Models\RealEstate\Property;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PropertySearchService
{
    public function search(array $filters = []): LengthAwarePaginator
    {
        return Property::query()
            ->with('city')
            ->published()
            ->when($filters['city'] ?? null, fn ($q, $city) => $q->whereHas('city', fn ($cityQ) => $cityQ->where('slug', $city)))
            ->when($filters['guests'] ?? null, fn ($q, $guests) => $q->where('max_guests', '>=', (int) $guests))
            ->latest('published_at')
            ->paginate(12);
    }
}
