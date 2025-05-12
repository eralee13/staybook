<?php

namespace App\Http\Requests\API\V1_1;

use Illuminate\Foundation\Http\FormRequest;

class SearchOneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation cancellations that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'adult' => 'required|integer',
            'children_ages' => 'array',
            'arrivalDate' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'departureDate' => 'required|date|date_format:Y-m-d|after_or_equal:check_in',
        ];
    }
}
