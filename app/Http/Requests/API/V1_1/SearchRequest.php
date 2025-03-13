<?php

namespace App\Http\Requests\API\V1_1;

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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hotel_ids' => 'array|required|exists:hotels,id',
            'residency' => 'required|string',
            'adults' => 'required|integer',
            'children_ages' => 'array',
            'check_in' => 'required|date|date_format:Y-m-d|after_or_equal:today',
            'check_out' => 'required|date|date_format:Y-m-d|after_or_equal:check_in',
        ];
    }
}
