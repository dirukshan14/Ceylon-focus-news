<?php

namespace App\Http\Requests;

class UpdateArticleRequest extends StoreArticleRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('article'));
    }
}
