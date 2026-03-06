<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">
@foreach($items as $item)
    <url>
        <loc>{{ $item['loc'] }}</loc>
        @if(!empty($item['lastmod']))<lastmod>{{ $item['lastmod'] }}</lastmod>@endif
        @foreach(($item['alternates'] ?? []) as $locale => $href)
            <xhtml:link rel="alternate" hreflang="{{ $locale }}" href="{{ $href }}"/>
        @endforeach
        @foreach(($item['images'] ?? []) as $imageUrl)
            <image:image><image:loc>{{ $imageUrl }}</image:loc></image:image>
        @endforeach
    </url>
@endforeach
</urlset>
