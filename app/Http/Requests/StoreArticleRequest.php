<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Article::class);
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'body' => ['required', 'string', 'max:500000'],
            'featured_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'gallery' => ['nullable', 'array', 'max:20'],
            'gallery.*' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],

            'category_id' => ['required', 'exists:categories,id'],
            'subcategory_id' => ['nullable', 'exists:categories,id', 'different:category_id'],
            'tags' => ['nullable', 'array', 'max:20'],
            'tags.*' => ['string', 'max:50'],

            'status' => ['required', 'in:draft,pending_review,scheduled,published,archived'],
            'published_at' => ['nullable', 'date'],
            'is_featured' => ['boolean'],
            'is_breaking' => ['boolean'],
            'is_trending' => ['boolean'],

            'seo_title' => ['nullable', 'string', 'max:255'],
            'seo_description' => ['nullable', 'string', 'max:500'],
            'seo_keywords' => ['nullable', 'string', 'max:255'],
            'og_image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'canonical_url' => ['nullable', 'url:http,https', 'max:255'],
        ];
    }

    /**
     * Scheduled articles must have a future publish date.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->input('status') === 'scheduled') {
                if (! $this->filled('published_at')) {
                    $validator->errors()->add('published_at', 'A publish date is required for scheduled articles.');
                } elseif (\Illuminate\Support\Carbon::parse($this->input('published_at'))->isPast()) {
                    $validator->errors()->add('published_at', 'The scheduled date must be in the future.');
                }
            }
        });
    }
}
