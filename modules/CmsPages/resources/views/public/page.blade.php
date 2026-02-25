@extends('layouts.public')

@section('meta_title', $translation?->meta_title ?: $translation?->title)
@section('meta_description', $translation?->meta_description ?: ($translation?->excerpt ?? ''))
@section('canonical', $translation?->canonical_url ?: url()->current())

@section('content')
@php($metaTitle = $translation?->meta_title ?: $translation?->title)
@php($data = $translation?->template_data ?? [])
@if($page->template === 'home')
    <section class="mb-6 rounded border bg-white p-6">
        <h1 class="text-3xl font-bold">{{ data_get($data,'hero.title', $translation?->title) }}</h1>
        <p class="mt-2">{{ data_get($data,'hero.subtitle', $translation?->excerpt) }}</p>
        <div class="mt-3 flex gap-2">
            <a href="{{ data_get($data,'hero.cta_url','/contact-us') }}" class="rounded bg-blue-600 px-4 py-2 text-white">{{ data_get($data,'hero.cta_label','Request a quote') }}</a>
            <a href="/our-properties" class="rounded bg-slate-900 px-4 py-2 text-white">Our properties</a>
        </div>
    </section>

    <section class="mb-6 rounded border bg-white p-6">
        <h2 class="text-xl font-semibold">Services</h2>
        <p>{{ data_get($data,'services_teaser.text', $translation?->content) }}</p>
    </section>

    <section class="mb-6 rounded border bg-white p-6">
        <h2 class="mb-3 text-xl font-semibold">Featured properties</h2>
        <div class="grid gap-3 md:grid-cols-3">
            @foreach($featuredProperties as $property)
                <a class="rounded border p-3" href="{{ url('/properties/'.$property->slug) }}">{{ $property->translated()?->title }}</a>
            @endforeach
        </div>
    </section>

    <section class="rounded border bg-white p-6">
        <h2 class="text-xl font-semibold">Final CTA</h2>
        <a href="/contact-us" class="mt-2 inline-block rounded bg-blue-600 px-4 py-2 text-white">Contact us</a>
    </section>
@else
    <section class="rounded border bg-white p-6">
        <h1 class="text-3xl font-bold">{{ $translation?->title }}</h1>
        <div class="prose mt-4 max-w-none">{!! nl2br(e($translation?->content)) !!}</div>

        @if($page->template === 'contact')
            <div class="mt-6 grid gap-6 md:grid-cols-2">
                <form method="POST" action="{{ route('forms.contact.submit') }}" class="space-y-2 rounded border p-4">
                    @csrf
                    <h3 class="font-semibold">Contact form</h3>
                    <input name="first_name" class="w-full rounded border p-2" placeholder="First name" required>
                    <input name="last_name" class="w-full rounded border p-2" placeholder="Last name" required>
                    <input name="phone" class="w-full rounded border p-2" placeholder="Phone">
                    <input name="email" type="email" class="w-full rounded border p-2" placeholder="Email" required>
                    <select name="subject_type" class="w-full rounded border p-2"><option value="owner">Owner</option><option value="traveler">Traveler</option><option value="other">Other</option></select>
                    <input name="website_url" class="w-full rounded border p-2" placeholder="Website URL">
                    <textarea name="message" class="w-full rounded border p-2" placeholder="Message" required></textarea>
                    <input name="company_name" class="hidden" autocomplete="off" tabindex="-1">
                    <input type="hidden" name="submitted_at" value="{{ time() }}">
                    <button class="rounded bg-blue-600 px-4 py-2 text-white">Send</button>
                </form>

                <form method="POST" action="{{ route('forms.quote.submit') }}" class="space-y-2 rounded border p-4">
                    @csrf
                    <h3 class="font-semibold">Quote request</h3>
                    <input name="full_name" class="w-full rounded border p-2" placeholder="Full name" required>
                    <input name="email" type="email" class="w-full rounded border p-2" placeholder="Email" required>
                    <input name="phone" class="w-full rounded border p-2" placeholder="Phone">
                    <input name="property_interest" class="w-full rounded border p-2" placeholder="Property interest">
                    <textarea name="message" class="w-full rounded border p-2" placeholder="Message" required></textarea>
                    <input name="preferred_contact_method" class="w-full rounded border p-2" placeholder="Preferred contact method">
                    <input name="company_name" class="hidden" autocomplete="off" tabindex="-1">
                    <input type="hidden" name="submitted_at" value="{{ time() }}">
                    <button class="rounded bg-slate-900 px-4 py-2 text-white">Request quote</button>
                </form>
            </div>
        @endif
    </section>
@endif
@endsection
