<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class Advertisement extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name', 'type', 'image', 'html_code', 'click_url', 'position',
        'device_target', 'is_active', 'priority', 'starts_at', 'ends_at',
        'impressions_count', 'clicks_count', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function impressions(): HasMany
    {
        return $this->hasMany(AdImpression::class);
    }

    public function clicksLog(): HasMany
    {
        return $this->hasMany(AdClick::class);
    }

    /**
     * Ads currently eligible to display: active, within date range, matching device.
     */
    public function scopeLiveForPosition(Builder $query, string $position, string $device = 'all'): Builder
    {
        $now = Carbon::now();

        return $query->where('position', $position)
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now))
            ->where(fn ($q) => $q->where('device_target', 'all')->orWhere('device_target', $device))
            ->orderByDesc('priority');
    }

    public function recordImpression(): void
    {
        $this->increment('impressions_count');
        AdImpression::query()->firstOrCreate(
            ['advertisement_id' => $this->id, 'date' => Carbon::today()->toDateString()],
            ['count' => 0]
        )->increment('count');
    }

    public function recordClick(): void
    {
        $this->increment('clicks_count');
        AdClick::query()->firstOrCreate(
            ['advertisement_id' => $this->id, 'date' => Carbon::today()->toDateString()],
            ['count' => 0]
        )->increment('count');
    }

    public function ctr(): float
    {
        if ($this->impressions_count === 0) {
            return 0.0;
        }

        return round(($this->clicks_count / $this->impressions_count) * 100, 2);
    }
}
