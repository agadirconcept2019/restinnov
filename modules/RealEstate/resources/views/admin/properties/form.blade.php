@extends('layouts.admin')
@section('content')
<h2 class="mb-4 text-2xl font-semibold">{{ $property->exists ? 'Edit' : 'Create' }} property</h2>
<form method="POST" action="{{ $property->exists ? route('admin.real-estate.properties.update',$property) : route('admin.real-estate.properties.store') }}" class="space-y-6">
    @csrf
    @if($property->exists) @method('PUT') @endif

    <section class="rounded border bg-white p-4"><h3 class="mb-2 font-semibold">1. Général</h3>
        <input name="slug" class="mb-2 w-full rounded border p-2" value="{{ old('slug',$property->slug) }}" placeholder="slug">
        <select name="status" class="w-full rounded border p-2">@foreach(['draft','published','archived'] as $s)<option value="{{ $s }}" @selected(old('status',$property->status?:'draft')===$s)>{{ $s }}</option>@endforeach</select>
    </section>

    <section class="rounded border bg-white p-4"><h3 class="mb-2 font-semibold">2. Localisation</h3>
        <select name="city_id" class="mb-2 w-full rounded border p-2">@foreach($cities as $city)<option value="{{ $city->id }}" @selected(old('city_id',$property->city_id)===$city->id)>{{ $city->translated()?->name }}</option>@endforeach</select>
        <select name="area_id" class="w-full rounded border p-2"><option value="">-- area --</option>@foreach($areas as $area)<option value="{{ $area->id }}" @selected(old('area_id',$property->area_id)===$area->id)>{{ $area->translated()?->name }}</option>@endforeach</select>
    </section>

    <section class="rounded border bg-white p-4"><h3 class="mb-2 font-semibold">3. Détails</h3>
        <div class="grid grid-cols-2 gap-2"><input name="max_guests" type="number" class="rounded border p-2" value="{{ old('max_guests',$property->max_guests??1) }}" placeholder="guests"><input name="bedrooms" type="number" class="rounded border p-2" value="{{ old('bedrooms',$property->bedrooms??1) }}" placeholder="bedrooms"><input name="beds" type="number" class="rounded border p-2" value="{{ old('beds',$property->beds??1) }}" placeholder="beds"><input name="bathrooms" type="number" class="rounded border p-2" value="{{ old('bathrooms',$property->bathrooms??1) }}" placeholder="bathrooms"></div>
    </section>

    <section class="rounded border bg-white p-4"><h3 class="mb-2 font-semibold">4. Prix & horaires</h3>
        <div class="grid grid-cols-2 gap-2"><input name="base_price_per_night" type="number" step="0.01" class="rounded border p-2" value="{{ old('base_price_per_night',$property->base_price_per_night??0) }}" placeholder="price"><input name="currency" class="rounded border p-2" value="{{ old('currency',$property->currency??'MAD') }}" placeholder="MAD"><input name="checkin_from" type="time" class="rounded border p-2" value="{{ old('checkin_from',$property->checkin_from) }}"><input name="checkout_until" type="time" class="rounded border p-2" value="{{ old('checkout_until',$property->checkout_until) }}"></div>
    </section>

    <section class="rounded border bg-white p-4"><h3 class="mb-2 font-semibold">5/6/7. Galerie, Amenities, Disponibilités</h3>
        <p class="mb-2 text-sm text-slate-600">Galerie: structure backend prête (table property_images).</p>
        <div class="grid grid-cols-2 gap-1">@foreach($amenities as $a)<label><input type="checkbox" name="amenity_ids[]" value="{{ $a->id }}" @checked(in_array($a->id, old('amenity_ids',$property->amenities->pluck('id')->all()??[])))> {{ $a->translated()?->name }}</label>@endforeach</div>
    </section>

    <section class="rounded border bg-white p-4"><h3 class="mb-2 font-semibold">8. Traductions</h3>
        @php($en = $property->translations->firstWhere('locale','en'))
        @php($fr = $property->translations->firstWhere('locale','fr'))
        <input name="title_en" class="mb-2 w-full rounded border p-2" value="{{ old('title_en',$en->title ?? '') }}" placeholder="Title EN">
        <textarea name="description_en" class="mb-2 w-full rounded border p-2" placeholder="Description EN">{{ old('description_en',$en->description ?? '') }}</textarea>
        <input name="title_fr" class="mb-2 w-full rounded border p-2" value="{{ old('title_fr',$fr->title ?? '') }}" placeholder="Title FR">
        <textarea name="description_fr" class="w-full rounded border p-2" placeholder="Description FR">{{ old('description_fr',$fr->description ?? '') }}</textarea>
    </section>

    <section class="rounded border bg-white p-4"><h3 class="mb-2 font-semibold">9. Publication</h3>
        <label><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured',$property->is_featured))> Featured</label>
        <input name="published_at" type="datetime-local" class="mt-2 w-full rounded border p-2" value="{{ old('published_at', optional($property->published_at)->format('Y-m-d\TH:i')) }}">
    </section>

    <div class="flex gap-2"><button class="rounded bg-blue-600 px-4 py-2 text-white">Save</button>@if($property->exists)<button form="delete-property" class="rounded bg-red-600 px-4 py-2 text-white" type="submit">Delete</button>@endif</div>
</form>
@if($property->exists)
<form id="delete-property" method="POST" action="{{ route('admin.real-estate.properties.destroy',$property) }}">@csrf @method('DELETE')</form>
@endif
@endsection
