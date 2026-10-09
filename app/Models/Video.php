<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Support\HasUnicodeSlug;

class Video extends Model
{
    use HasUnicodeSlug, SoftDeletes;

    protected string $slugSourceField = 'title';

    protected $fillable = [
        'title', 'slug', 'description', 'source_url', 'embed_url', 'start_seconds', 'provider',
        'thumbnail', 'category_id', 'author_id', 'status', 'is_featured',
        'published_at', 'article_id', 'views_count',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    /**
     * Detect the provider and build a safe iframe-embeddable URL from a
     * pasted share/watch URL. Called from the admin form request/service
     * before saving. See App\Services\VideoEmbedResolver.
     */
    public static function resolveProviderAndEmbed(string $url): array
    {
        return app(\App\Services\VideoEmbedResolver::class)->resolve($url);
    }

    /**
     * The URL actually used in the <iframe>/player - automatically includes
     * the start timestamp (start_seconds) when one is set, so a viewer lands
     * directly on the relevant moment (e.g. a specific person's segment in a
     * long funeral livestream recording) without manual seeking.
     */
    public function playbackUrl(): string
    {
        if (empty($this->embed_url) || empty($this->start_seconds)) {
            return $this->embed_url;
        }

        return match ($this->provider) {
            'youtube' => $this->embed_url.(str_contains($this->embed_url, '?') ? '&' : '?').'start='.$this->start_seconds.'&autoplay=0',
            'vimeo' => $this->embed_url.'#t='.$this->start_seconds.'s',
            default => $this->embed_url,
        };
    }

    public function formattedStartTime(): ?string
    {
        if (empty($this->start_seconds)) {
            return null;
        }

        $h = intdiv($this->start_seconds, 3600);
        $m = intdiv($this->start_seconds % 3600, 60);
        $s = $this->start_seconds % 60;

        return $h > 0
            ? sprintf('%d:%02d:%02d', $h, $m, $s)
            : sprintf('%d:%02d', $m, $s);
    }

    /* ---------------- Relationships ---------------- */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'tag_video');
    }

    /* ---------------- Scopes ---------------- */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Full public URL for the uploaded thumbnail, or null if none was set
     * (views fall back to a placeholder image in that case).
     */
    public function thumbnailUrl(): ?string
    {
        return $this->thumbnail ? asset('storage/'.$this->thumbnail) : null;
    }
}
