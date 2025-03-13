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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hotel_id' => 'required|exists:hotels,id',
            'room_id' => 'required|exists:rooms,id',
            'rate_id' => 'exists:rates,id',
            'client_reference_id' => 'string',
            'phone' => 'required|string',
            'email' => 'required|string|email',
            'sum' => 'required|numeric',
            'payment' => 'string',
            'title' => 'required|string',
            'adult' => 'integer',
            //'last_name' => 'required|string',
            //'is_child' => 'required|boolean',
            'comment' => 'string',
            'arrivalDate' => 'required|date',
            'departureDate' => 'required|date',
            'book_token' => 'string',
            'user_id' => 'integer|exists:users,id',
            'status' => 'string'
        ];
    }
}
