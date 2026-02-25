<?php

namespace App\Http\Controllers\Admin\Core;

use App\Core\Media\MediaVariantService;
use App\Http\Controllers\Controller;
use App\Models\Core\Media;
use Illuminate\Http\Request;

class MediaController extends Controller
{
    public function index()
    {
        $media = Media::query()
            ->when(request('type'), fn ($q, $value) => $q->where('mime_type', 'like', $value.'/%'))
            ->latest()
            ->paginate(24)
            ->withQueryString();

        return view('admin.media.index', compact('media'));
    }

    public function store(Request $request, MediaVariantService $mediaVariantService)
    {
        $request->validate(['file' => ['required', 'file', 'max:10240']]);
        $mediaVariantService->createFromUpload($request->file('file'), auth()->id());

        return back()->with('status', 'Media uploaded.');
    }

    public function update(Request $request, Media $media)
    {
        $validated = $request->validate([
            'alt' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:2000'],
        ]);

        $media->update($validated);

        return back()->with('status', 'Media metadata updated.');
    }

    public function destroy(Media $media)
    {
        $media->delete();

        return back()->with('status', 'Media deleted.');
    }
}
