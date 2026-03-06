<?php

namespace App\Http\Controllers\Core;

use App\Core\Sitemap\SitemapRegistry;
use App\Http\Controllers\Controller;

class SitemapController extends Controller
{
    public function __invoke(SitemapRegistry $registry)
    {
        return response($registry->renderXml(), 200, ['Content-Type' => 'application/xml; charset=UTF-8']);
    }
}
