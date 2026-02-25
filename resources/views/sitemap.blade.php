<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach($items as $item)
    <url>
        <loc>{{ $item['loc'] }}</loc>
        @if(!empty($item['lastmod']))<lastmod>{{ $item['lastmod'] }}</lastmod>@endif
        @foreach(($item['alternates'] ?? []) as $locale => $href)
            <xhtml:link rel="alternate" hreflang="{{ $locale }}" href="{{ $href }}"/>
        @endforeach
    </url>
@endforeach
</urlset>
