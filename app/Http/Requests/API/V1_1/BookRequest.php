<?php

namespace App\Http\Requests\API\V1_1;

use Illuminate\Foundation\Http\FormRequest;

class BookRequest extends FormRequest
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
            'hotel_id' => 'required|exists:hotels,id',
            'rate_id' => 'required|exists:rates,id',
            'client_reference_id' => 'required',
            'firstname' => 'required',
            'lastname' => 'required',
            'is_child' => 'required|boolean',
            'email' => 'required|string|email',
            'phone' => 'required|string',
            'price' => 'required|integer|min:1',
            'payment' => 'string|nullable',
            'room_id' => 'integer|exists:rooms,id',
            'comment' => 'string',
            'arrivalDate' => 'required|date|date_format:Y-m-d',
            'departureDate' => 'required|date|date_format:Y-m-d',
            'adult' => 'required|integer|min:1',
            'childages' => 'string',
            'cancellation_rule_id' => 'integer|exists:cancellation_rules,id',
            //'user_id' => 'required|integer|exists:users,id',
        ];
    }
}
