<?php

namespace App\Http\Controllers\Admin\Core;

use App\Core\Cache\CacheVersionManager;
use App\Http\Controllers\Controller;
use App\Models\Core\Menu;
use App\Models\Core\MenuItem;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::query()->with('items')->orderBy('name')->paginate(20);

        return view('admin.menus.index', compact('menus'));
    }

    public function reorder(Request $request, Menu $menu, CacheVersionManager $cacheVersionManager)
    {
        $validated = $request->validate([
            'items' => ['required', 'array'],
            'items.*.id' => ['required', 'exists:menu_items,id'],
            'items.*.parent_id' => ['nullable', 'integer'],
            'items.*.sort_order' => ['required', 'integer', 'min:0'],
        ]);

        foreach ($validated['items'] as $item) {
            if (! empty($item['parent_id']) && (int) $item['parent_id'] === (int) $item['id']) {
                return back()->withErrors(['items' => 'A menu item cannot be its own parent.']);
            }

            MenuItem::query()
                ->where('menu_id', $menu->id)
                ->whereKey($item['id'])
                ->update([
                    'parent_id' => $item['parent_id'] ?? null,
                    'sort_order' => $item['sort_order'],
                ]);
        }

        $cacheVersionManager->bump('menus.'.$menu->slug);

        return back()->with('status', 'Menu order updated.');
    }
}
