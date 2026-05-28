<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActiviteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'nom' => 'required|string|max:255',
            'description' => 'nullable|string',
            'prix' => 'required|numeric|min:0',
            'destination_id' => 'required|exists:destinations,id',
            'adapte_enfants' => 'boolean',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
        ];

        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['nom'] = 'sometimes|string|max:255';
            $rules['prix'] = 'sometimes|numeric|min:0';
            $rules['destination_id'] = 'sometimes|exists:destinations,id';
        }

        return $rules;
    }
}