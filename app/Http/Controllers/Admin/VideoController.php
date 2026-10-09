<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Video;
use App\Services\VideoEmbedResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function __construct(private VideoEmbedResolver $resolver)
    {
        $this->middleware('permission:manage-videos');
    }

    public function index(Request $request)
    {
        $query = Video::with(['category', 'author']);

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        $videos = $query->latest()->paginate(20)->withQueryString();

        return view('admin.videos.index', compact('videos'));
    }

    public function create()
    {
        $categories = Category::active()->orderBy('name')->get();

        return view('admin.videos.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validateVideo($request);

        $resolved = $this->resolver->resolve($data['source_url']);
        $data['provider'] = $resolved['provider'];
        $data['embed_url'] = $resolved['embed_url'];
        $data['author_id'] = $request->user()->id;

        if ($request->hasFile('thumbnail')) {
            $data['thumbnail'] = $request->file('thumbnail')->store('videos/thumbnails', 'public');
        }

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = now();
        }

        $video = Video::create($data);
        AuditLog::record('video.created', $video);

        return redirect()->route('admin.videos.index')->with('success', 'Video added.');
    }

    public function edit(Video $video)
    {
        $categories = Category::active()->orderBy('name')->get();

        return view('admin.videos.edit', compact('video', 'categories'));
    }

    public function update(Request $request, Video $video)
    {
        $data = $this->validateVideo($request);

        // Re-resolve the embed only if the URL actually changed.
        if ($data['source_url'] !== $video->source_url) {
            $resolved = $this->resolver->resolve($data['source_url']);
            $data['provider'] = $resolved['provider'];
            $data['embed_url'] = $resolved['embed_url'];
        }

        if ($request->hasFile('thumbnail')) {
            if ($video->thumbnail) {
                Storage::disk('public')->delete($video->thumbnail);
            }
            $data['thumbnail'] = $request->file('thumbnail')->store('videos/thumbnails', 'public');
        }

        $video->update($data);
        AuditLog::record('video.updated', $video);

        return redirect()->route('admin.videos.index')->with('success', 'Video updated.');
    }

    public function destroy(Video $video)
    {
        AuditLog::record('video.deleted', $video, ['title' => $video->title]);
        $video->delete();

        return redirect()->route('admin.videos.index')->with('success', 'Video deleted.');
    }

    private function validateVideo(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'source_url' => ['required', 'url:http,https', 'max:500'],
            'thumbnail' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'start_seconds' => ['nullable', 'integer', 'min:0', 'max:86400'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'article_id' => ['nullable', 'exists:articles,id'],
            'status' => ['required', 'in:draft,published,archived'],
            'is_featured' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ]);
    }
}
