<?php

namespace App\Http\Controllers\Api\V1\Public;

use App\Http\Controllers\Api\V1\ApiController;
use App\Http\Resources\Api\V1\PageResource;
use App\Models\CmsPages\Page;

class PagePublicApiController extends ApiController
{
    public function show(string $slug)
    {
        $this->resolveLocale();

        $page = Page::query()->published()->with('translations')->where('slug', $slug)->firstOrFail();

        return PageResource::make($page);
    }
}
