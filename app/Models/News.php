<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class News extends Model
{
    use HasFactory;

    protected $table = 'news';

    protected $fillable = [
        'title', 'slug', 'image', 'excerpt', 'content',
        'category_id', 'user_id', 'status', 'views', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($news) {
            if (empty($news->slug)) {
                $news->slug = Str::slug($news->title) . '-' . Str::random(6);
            }
            if (empty($news->published_at) && $news->status === 'published') {
                $news->published_at = now();
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
                      ->where('published_at', '<=', now());
    }

    public function scopeLatestNews($query, $limit = 5)
    {
        return $query->published()->orderByDesc('published_at')->limit($limit);
    }

    // Related news: same category, excluding current article
    public function related($limit = 4)
    {
        return News::published()
            ->where('category_id', $this->category_id)
            ->where('id', '!=', $this->id)
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getImageUrlAttribute(): string
    {
        return $this->image
            ? asset('storage/' . $this->image)
            : asset('images/news-placeholder.jpg');
    }
}
