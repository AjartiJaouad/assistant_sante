<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSymptomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // L'autorisation est déjà gérée par Sanctum dans les routes
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'severity' => 'required|in:mild,moderate,severe',
            'description' => 'nullable|string',
            'date_recorded' => 'required|date',
            'notes' => 'nullable|string',
        ];
    }
}
