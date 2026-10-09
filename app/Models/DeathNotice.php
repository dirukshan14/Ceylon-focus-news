<?php

namespace App\Models;

use App\Support\HasUnicodeSlug;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

class DeathNotice extends Model
{
    use HasUnicodeSlug, SoftDeletes;

    protected string $slugSourceField = 'name';

    protected $fillable = [
        'name', 'slug', 'photo', 'age', 'location',
        'date_of_birth', 'date_of_death',
        'funeral_date', 'funeral_time', 'funeral_venue',
        'survived_by', 'tribute_message',
        'contact_name', 'contact_phone',
        'video_id', 'status', 'views_count', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'date_of_death' => 'date',
            'funeral_date' => 'date',
        ];
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function incrementViews(): void
    {
        $this->increment('views_count');
    }

    /**
     * Age at time of death - uses the stored age if provided, otherwise
     * calculates it from date_of_birth/date_of_death when both are known.
     */
    public function displayAge(): ?int
    {
        if ($this->age) {
            return $this->age;
        }

        if ($this->date_of_birth && $this->date_of_death) {
            return (int) $this->date_of_birth->diffInYears($this->date_of_death);
        }

        return null;
    }

    public function hasFuneralDetails(): bool
    {
        return $this->funeral_date || $this->funeral_venue || $this->funeral_time;
    }
}
