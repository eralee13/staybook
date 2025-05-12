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
            'room_id' => 'required|exists:rooms,id',
            'rate_id' => 'required|exists:rates,id',
            'user_id' => 'required|integer|exists:users,id',
            'adult' => 'required|integer',
            'childages' => 'string',
            'title' => 'required|string',
            'phone' => 'required|string',
            'email' => 'required|string|email',
            'sum' => 'required|integer|min:1',
            'comment' => 'string',
            'arrivalDate' => 'required|date|date_format:Y-m-d',
            'departureDate' => 'required|date|date_format:Y-m-d',
            'book_token' => 'required|string',
            'status' => 'string|in:pending,reserved,cancelled',
        ];
    }
}
