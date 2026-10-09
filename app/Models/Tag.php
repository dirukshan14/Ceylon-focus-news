<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Support\HasUnicodeSlug;

class Tag extends Model
{
    use HasUnicodeSlug;

    protected string $slugSourceField = 'name';

    protected $fillable = ['name', 'slug'];

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class, 'article_tag');
    }

    public function videos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class, 'tag_video');
    }
}
