<?php

namespace App\Core\Sitemap;

interface SitemapProviderInterface
{
    /** @return array<int,array{loc:string,lastmod:?string,alternates?:array<string,string>}> */
    public function items(): array;
}
