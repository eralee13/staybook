<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class ActualizeRequest extends FormRequest
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
            'start'   => ['required','date'],
            'end'     => ['required','date','after:start_d'],
            'citizen' => 'string|min:2',
            //'hotel_id'  => ['nullable','integer','exists:hotels,id'],
            //'room_id'   => ['nullable','integer','exists:rooms,id'],
            //'rate_ids'  => ['nullable','array'],
            //'rate_ids.*'=> ['integer','exists:rates,id'],
            'region' => 'required|string',
            'adult'     => ['required','nullable','integer','min:1'],
            'child' => 'integer|min:0',
            'childages' => 'array|nullable',
            'persist'   => ['nullable','boolean'], // true — перезаписать availability
        ];
    }
}
