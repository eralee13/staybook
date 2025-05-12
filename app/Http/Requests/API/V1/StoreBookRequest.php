<?php

namespace App\Http\Requests\API\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
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
            'hotel_id' => 'required|integer|exists:hotels,id',
            'room_id' => 'required|integer|exists:rooms,id',
            'title' => 'required|min:3|max:255',
            'title2' => 'string|min:3|max:255',
            'phone' => 'required|min:10|max:15',
            'email' => 'required|email',
            'adult' => 'required|integer|min:1',
            'child' => 'nullable',
            'arrivalDate' => 'required|date|after_or_equal:today|date_format:Y-m-d H:i:s',
            'departureDate' => 'required|date|after_or_equal:arrivalDate|date_format:Y-m-d H:i:s',
            'sum' => 'nullable|integer|min:1',
            'user_id' => 'required|integer|exists:users,id',
            'book_token' => 'required|string|min:15|max:15',
            'status' => 'string|min:3|max:255',
        ];
    }
}
