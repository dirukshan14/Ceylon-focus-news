<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * A drop-in replacement for spatie/laravel-sluggable's HasSlug trait.
 *
 * Why this exists: Laravel's Str::slug() / Str::ascii() transliterates text
 * to the printable ASCII range and strips everything else. That works fine
 * for English/French/German etc. (accented Latin letters have a mapping
 * table) but Tamil, Sinhala, and many other scripts have no such mapping -
 * the characters just get deleted, leaving an empty or meaningless slug.
 * spatie/laravel-sluggable's usingLanguage() only wraps Str::slug(), so it
 * can't avoid this either, and some installed versions won't even accept
 * null to skip the ascii step.
 *
 * This trait generates slugs using \p{L} (any Unicode letter) so Tamil,
 * Sinhala, or any other script's own characters are kept in the URL.
 *
 * Usage in a model:
 *   use HasUnicodeSlug;
 *   protected string $slugSourceField = 'title'; // or 'name'
 */
trait HasUnicodeSlug
{
    public static function bootHasUnicodeSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = $model->generateUniqueUnicodeSlug();
            }
        });
    }

    protected function slugSourceField(): string
    {
        return property_exists($this, 'slugSourceField') ? $this->slugSourceField : 'title';
    }

    protected function generateUniqueUnicodeSlug(): string
    {
        $source = (string) ($this->{$this->slugSourceField()} ?? '');
        $base = static::toUnicodeSlug($source);

        $slug = $base;
        $suffix = 1;

        while (static::withTrashedIfSupported()->where('slug', $slug)->exists()) {
            $suffix++;
            $slug = $base.'-'.$suffix;
        }

        return $slug;
    }

    /**
     * Use withTrashed() when the model has soft deletes, so a slug from a
     * trashed record isn't accidentally reused - otherwise fall back to a
     * plain query.
     */
    protected static function withTrashedIfSupported()
    {
        $instance = new static;

        return method_exists($instance, 'withTrashed')
            ? static::withTrashed()
            : static::query();
    }

    /**
     * Convert any string (any script) into a URL-safe slug while preserving
     * the original letters/numbers. Only whitespace, punctuation, and
     * symbols are stripped or converted to the separator.
     */
    public static function toUnicodeSlug(string $title, string $separator = '-'): string
    {
        $title = mb_strtolower(trim($title), 'UTF-8');

        // Collapse whitespace/underscores into the separator.
        $title = preg_replace('/[\s_]+/u', $separator, $title);

        // Strip anything that isn't a Unicode letter, a Unicode mark, a
        // Unicode number, or the separator itself. \p{M} (marks) is the
        // critical piece here: Tamil (and Sinhala, Hindi, etc.) vowel signs
        // like ொ, ி, ு, ா are attached as *combining marks*, not letters -
        // Unicode classifies them as category M, not L. Without \p{M},
        // this regex silently deleted every vowel sign and left only bare
        // consonants, so "கொழும்பில்" became "கழமபல" - different words
        // with different vowels but the same consonants then collapsed
        // onto the *same* garbled slug and collided on the unique index.
        $quotedSeparator = preg_quote($separator, '/');
        $title = preg_replace('/[^\p{L}\p{M}\p{N}'.$quotedSeparator.']+/u', '', $title);

        // Collapse repeated separators and trim leading/trailing ones.
        $title = preg_replace('/'.$quotedSeparator.'+/u', $separator, $title);
        $title = trim($title, $separator);

        // Extremely rare fallback (e.g. an emoji-only title) so we never
        // save an empty slug.
        return $title !== '' ? $title : Str::random(8);
    }
}
