<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class HotelRequest extends FormRequest
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
            'adresse'          => 'nullable|string',
            'etoiles'          => $isUpdate ? 'sometimes|integer|min:1|max:5' : 'required|integer|min:1|max:5',
            'ville_id'         => $isUpdate ? 'sometimes|exists:villes,id' : 'required|exists:villes,id',
            'image_principale' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ];
    }
}