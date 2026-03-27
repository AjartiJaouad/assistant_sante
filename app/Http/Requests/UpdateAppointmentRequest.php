<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => ['sometimes', 'required', 'exists:doctors,id'],
            'appointment_date' => ['sometimes', 'required', 'date', 'after:now'],
            'status' => ['sometimes', 'required', 'in:pending,confirmed,cancelled'],
            'notes' => ['nullable', 'string'],
        ];
    }
}
