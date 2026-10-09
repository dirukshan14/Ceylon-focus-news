<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class BreakingNews extends Model
{
    protected $table = 'breaking_news';

    protected $fillable = [
        'text', 'article_id', 'url', 'priority', 'is_active', 'starts_at', 'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    public function scopeLive(Builder $query): Builder
    {
        $now = Carbon::now();

        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->orderByDesc('priority');
    }

    public function linkUrl(): string
    {
        if ($this->article) {
            return route('news.show', $this->article->slug);
        }

        // SECURITY: never output javascript:/data: URLs into an href.
        return ($this->url && preg_match('#^(https?://|/)#i', $this->url)) ? $this->url : '#';
    }
}
