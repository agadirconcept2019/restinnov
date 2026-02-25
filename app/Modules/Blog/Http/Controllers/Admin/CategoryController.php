<?php

namespace App\Modules\Blog\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Modules\Blog\Http\Requests\Admin\StoreCategoryRequest;
use App\Modules\Blog\Models\PostCategory;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = PostCategory::query()->with('translations')->orderBy('sort_order')->paginate(20);

        return view('blog::admin.categories.index', compact('categories'));
    }

    public function store(StoreCategoryRequest $request)
    {
        $category = PostCategory::query()->create([
            'slug' => $request->input('slug'),
            'is_active' => (bool) $request->boolean('is_active', true),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        $this->syncTranslations($category, $request->validated());

        return back()->with('status', 'Category created.');
    }

    public function update(StoreCategoryRequest $request, PostCategory $category)
    {
        $category->update([
            'slug' => $request->input('slug'),
            'is_active' => (bool) $request->boolean('is_active', true),
            'sort_order' => (int) $request->input('sort_order', 0),
        ]);

        $this->syncTranslations($category, $request->validated());

        return back()->with('status', 'Category updated.');
    }

    public function destroy(PostCategory $category)
    {
        $category->delete();

        return back()->with('status', 'Category deleted.');
    }

    private function syncTranslations(PostCategory $category, array $data): void
    {
        foreach (['en', 'fr', 'es'] as $locale) {
            if (empty($data['name_'.$locale])) {
                continue;
            }

            $category->translations()->updateOrCreate(['locale' => $locale], ['name' => $data['name_'.$locale]]);
        }
    }
}
