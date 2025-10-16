<?php

namespace App\Http\Requests\API\V1;

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
            'arrivalDate' => 'required|date|after_or_equal:today|date_format:Y-m-d',
            'departureDate' => 'required|date|after_or_equal:arrivalDate|date_format:Y-m-d',
            'adult' => 'required|integer|min:1',
            'child' => 'integer|min:0',
            'childages' => 'nullable',
            'hotel_id' => 'required|integer|exists:hotels,id',
            'room_id' => 'required|integer|exists:rooms,id',
            'rate_id' => 'required|integer|exists:rates,id',
            'sum' => 'required|integer',
            'title' => 'required|min:3|max:255',
            'phone' => 'required|min:10|max:15',
            'email' => 'required|email',
            'book_token' => 'required|string|min:15|max:15',
            'user_id' => 'required|integer',
            'status' => 'required|string|in:Pending,Reserved,Cancelled',
        ];
    }
}
