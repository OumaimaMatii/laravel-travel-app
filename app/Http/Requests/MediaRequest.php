<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'titre' => 'nullable|string|max:255',
            'ordre' => 'nullable|integer',
            'est_principale' => 'boolean',
            'mediable_type' => 'required|string',
            'mediable_id' => 'required|integer',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ];
    }
}