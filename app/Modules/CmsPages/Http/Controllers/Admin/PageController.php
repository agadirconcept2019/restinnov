<?php

namespace App\Modules\CmsPages\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CmsPages\Page;
use App\Modules\CmsPages\Http\Requests\Admin\StorePageRequest;

class PageController extends Controller
{
    public function index()
    {
        $pages = Page::query()->with('translations')->latest()->paginate(20);

        return view('cmspages::admin.pages.index', compact('pages'));
    }

    public function create()
    {
        return view('cmspages::admin.pages.form', ['page' => new Page]);
    }

    public function store(StorePageRequest $request)
    {
        $page = Page::query()->create([
            'slug' => $request->input('slug'),
            'template' => $request->input('template'),
            'status' => $request->input('status'),
            'published_at' => $request->input('status') === 'published' ? ($request->input('published_at') ?: now()) : null,
            'author_id' => auth()->id(),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        $this->syncTranslations($page, $request->validated());

        return redirect()->route('admin.cms-pages.pages.edit', $page)->with('status', 'Page created.');
    }

    public function edit(Page $page)
    {
        $page->load('translations');

        return view('cmspages::admin.pages.form', compact('page'));
    }

    public function update(StorePageRequest $request, Page $page)
    {
        $page->update([
            'slug' => $request->input('slug'),
            'template' => $request->input('template'),
            'status' => $request->input('status'),
            'published_at' => $request->input('status') === 'published' ? ($request->input('published_at') ?: now()) : null,
            'updated_by' => auth()->id(),
        ]);

        $this->syncTranslations($page, $request->validated());

        return back()->with('status', 'Page updated.');
    }

    public function destroy(Page $page)
    {
        $page->delete();

        return redirect()->route('admin.cms-pages.pages.index')->with('status', 'Page deleted.');
    }

    private function syncTranslations(Page $page, array $data): void
    {
        foreach (['en', 'fr', 'es'] as $locale) {
            if (empty($data['title_'.$locale])) {
                continue;
            }

            $templateData = null;
            if (! empty($data['template_data_'.$locale])) {
                $templateData = json_decode($data['template_data_'.$locale], true);
            }

            $page->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $data['title_'.$locale],
                    'content' => $data['content_'.$locale] ?? null,
                    'template_data' => $templateData,
                    'meta_title' => $data['meta_title_'.$locale] ?? null,
                    'meta_description' => $data['meta_description_'.$locale] ?? null,
                    'canonical_url' => $data['canonical_url_'.$locale] ?? null,
                ],
            );
        }
    }
}
