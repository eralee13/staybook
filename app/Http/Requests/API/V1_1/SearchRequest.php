<?php

namespace App\Http\Requests\API\V1_1;

use Carbon\Carbon;
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

    protected function prepareForValidation()
    {
        $this->merge([
            'arrivalDate' => $this->input('arrivalDate', Carbon::today()->toDateString()),
        ]);
    }


    public function rules(): array
    {
        return [
            'city' => 'required|exists:cities,title',
            'arrivalDate' => 'required|date|date_format:Y-m-d',
            'departureDate' => 'required|date|date_format:Y-m-d|after_or_equal:arrivalDate',
            'adult' => 'required|integer',
            'children_ages' => 'array',
            'rating' => 'integer|between:1,5',
        ];
    }
}
