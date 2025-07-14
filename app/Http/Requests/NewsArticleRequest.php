<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class NewsArticleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'excerpt' => 'required|string|max:500',
            'content' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            // 'is_active' => 'boolean',
            // 'is_featured' => 'boolean',
            // 'is_for_mehsana'=>'boolean'
        ];

        // Add unique slug validation, excluding current article if updating
        if ($this->isMethod('POST')) {
            $rules['title'] .= '|unique:news_articles,title';
        } else {
            $rules['title'] .= '|unique:news_articles,title,' . $this->route('news')->id;
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Article title is required.',
            'title.unique' => 'An article with this title already exists.',
            'excerpt.required' => 'Article excerpt is required.',
            'excerpt.max' => 'Excerpt must not exceed 500 characters.',
            'content.required' => 'Article content is required.',
            'image.image' => 'Please upload a valid image file.',
            'image.mimes' => 'Image must be a JPEG, PNG, JPG, or GIF file.',
            'image.max' => 'Image size must not exceed 2MB.',
        ];
    }
}