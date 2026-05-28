<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DestinationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isUpdate = $this->isMethod('PUT')
                 || $this->isMethod('PATCH')
                 || $this->input('_method') === 'PUT';

        return [
            'nom'              => $isUpdate ? 'sometimes|string|max:255' : 'required|string|max:255',
            'pays'             => $isUpdate ? 'sometimes|string|max:255' : 'required|string|max:255',
            'ville_id'         => $isUpdate ? 'sometimes|exists:villes,id' : 'required|exists:villes,id',
            'actif'            => 'boolean',
            'image_couverture' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }
}