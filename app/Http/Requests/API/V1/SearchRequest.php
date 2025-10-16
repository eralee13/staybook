<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
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
            'arrivalDate' => 'required|date|after_or_equal:today|date_format:Y-m-d',
            'departureDate' => 'required|date|after_or_equal:arrivalDate|date_format:Y-m-d',
            'region' => 'required|string',
            //'region' => 'required|string|exists:cities,name',
            'citizen' => 'string|min:2',
            'adult' => 'required|integer|min:1',
            'child' => 'integer|min:0',
            'childages' => 'array|nullable',
        ];
    }
}
