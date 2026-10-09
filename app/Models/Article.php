<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use App\Support\HasUnicodeSlug;

class Article extends Model
{
    use HasUnicodeSlug, SoftDeletes;

    // Source field HasUnicodeSlug reads from when generating a new slug.
    protected string $slugSourceField = 'title';

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'featured_image',
        'category_id', 'subcategory_id', 'author_id',
        'status', 'is_featured', 'is_breaking', 'is_trending', 'published_at',
        'seo_title', 'seo_description', 'seo_keywords', 'og_image', 'canonical_url',
        'reading_time_minutes', 'views_count', 'shares_count',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'is_breaking' => 'boolean',
            'is_trending' => 'boolean',
        ];
    }


    /**
     * Auto-calculate reading time from the body whenever it's set.
     */
    public function setBodyAttribute(string $value): void
    {
        // SECURITY: strip scripts/event handlers/javascript: URLs but keep
        // normal article formatting (stored-XSS protection).
        $value = \App\Support\HtmlSanitizer::clean($value);
        $this->attributes['body'] = $value;
        $words = str_word_count(strip_tags($value));
        $this->attributes['reading_time_minutes'] = max(1, (int) ceil($words / 200));
    }

    /* ---------------- Relationships ---------------- */

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'subcategory_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ArticleImage::class)->orderBy('order');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'article_tag');
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    /* ---------------- Scopes ---------------- */

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', Carbon::now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeBreaking(Builder $query): Builder
    {
        return $query->where('is_breaking', true);
    }

    public function scopeTrending(Builder $query): Builder
    {
        return $query->where('is_trending', true);
    }

    public function scopePopular(Builder $query): Builder
    {
        return $query->orderByDesc('views_count');
    }

    public function scopeInCategory(Builder $query, int $categoryId): Builder
    {
        return $query->where(function ($q) use ($categoryId) {
            $q->where('category_id', $categoryId)
                ->orWhere('subcategory_id', $categoryId);
        });
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        // Escape LIKE wildcards so "%" / "_" typed by a visitor are literal.
        $like = '%'.addcslashes($term, '\\%_').'%';
        $driver = $query->getConnection()->getDriverName();

        return $query->where(function ($q) use ($term, $like, $driver) {
            // FULLTEXT only exists on MySQL/MariaDB/PostgreSQL (e.g. not SQLite used in tests).
            if (in_array($driver, ['mysql', 'mariadb', 'pgsql'], true)) {
                $q->whereFullText(['title', 'excerpt'], $term)
                    ->orWhere('title', 'like', $like);
            } else {
                $q->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like);
            }
        });
    }

    /* ---------------- Helpers ---------------- */

    public function isPublished(): bool
    {
        return $this->status === 'published'
            && $this->published_at !== null
            && $this->published_at->lessThanOrEqualTo(Carbon::now());
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    
public function excerptOrTruncatedBody(int $length = 150): string
{
    if (! empty($this->excerpt)) {
        $text = \App\Support\HtmlSanitizer::clean($this->excerpt);
    } else {
        $text = \App\Support\HtmlSanitizer::clean($this->body ?? '');
    }

    $text = html_entity_decode(
        strip_tags($text),
        ENT_QUOTES | ENT_HTML5,
        'UTF-8'
    );

    $text = preg_replace('/\s+/u', ' ', $text) ?? '';

    return Str::limit(trim($text), $length);
}
}
