<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'min:2'],
            'tenant_id' => ['required', 'integer', 'exists:tenants,id'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:50'],
            'content_type' => ['nullable', 'string'],
            'release_year' => ['nullable', 'integer'],
            'language' => ['nullable', 'string'],
            'min_rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
        ];
    }
}