<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $blogId = $this->route('blog');

        return [
            'title' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|max:255|unique:blogs,slug,' . $blogId,
            'content' => 'sometimes|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'status' => 'sometimes|in:draft,published',
        ];
    }
}
