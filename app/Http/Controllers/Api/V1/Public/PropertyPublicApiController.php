<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\PropertyResource;
use App\Models\RealEstate\Property;
use Illuminate\Support\Facades\Cache;

class PropertyPublicApiController extends ApiController
{
    public function index()
    {
        $this->resolveLocale();
        $cacheKey = 'api:v1:properties:'.md5(json_encode(request()->query()));

        $payload = Cache::remember($cacheKey, 60, function () {
            $query = Property::query()
                ->published()
                ->with(['translations', 'city.translations', 'type.translations', 'mode.translations', 'amenities.translations']);

            $query->when(request('city'), fn ($q, $v) => $q->whereHas('city.translations', fn ($sq) => $sq->where('name', 'like', "%{$v}%")));
            $query->when(request('type'), fn ($q, $v) => $q->whereHas('type.translations', fn ($sq) => $sq->where('name', 'like', "%{$v}%")));
            $query->when(request('guests'), fn ($q, $v) => $q->where('max_guests', '>=', (int) $v));
            $query->when(request('price_min'), fn ($q, $v) => $q->where('base_price_per_night', '>=', (float) $v));
            $query->when(request('price_max'), fn ($q, $v) => $q->where('base_price_per_night', '<=', (float) $v));
            $query->when(request('amenities'), function ($q, $v) {
                $amenities = array_filter(array_map('trim', explode(',', $v)));
                foreach ($amenities as $amenity) {
                    $q->whereHas('amenities.translations', fn ($sq) => $sq->where('name', 'like', "%{$amenity}%"));
                }
            });

            $checkin = request('checkin_date');
            $checkout = request('checkout_date');
            if ($checkin && $checkout) {
                $query->whereDoesntHave('availabilities', function ($sq) use ($checkin, $checkout) {
                    $sq->whereBetween('date', [$checkin, $checkout])->whereIn('status', ['booked', 'blocked', 'unavailable']);
                });
            }

            return $query->paginate((int) request('per_page', 15))->withQueryString();
        });

        return PropertyResource::collection($payload);
    }

    public function show(string $slug)
    {
        $this->resolveLocale();

        $property = Property::query()
            ->published()
            ->with(['translations', 'city.translations', 'type.translations', 'mode.translations', 'amenities.translations', 'images'])
            ->where('slug', $slug)
            ->firstOrFail();

        return PropertyResource::make($property);
    }
}
