<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'title' => 'required|min:3|max:255',
            'title_en' => 'required|min:3|max:255',
            'hotel_id' => 'required',
            'room_id' => 'required',
            'meal_id' => 'required',
            'bed_type' => 'required',
            'price' => 'required|numeric',
            'cancellation_rule_id' => 'required',
            'availability' => 'required',
            'adult' => 'required',
            'free_children_age' => 'required',
            'child_extra_fee' => 'required',
        ];
        return $rules;
    }

    public function messages()
    {
        return[
            'required'=>'Поле :attribute обязательно для ввода',
            'min' => 'Поле :attribute должно иметь минимум :min символов',
        ];
    }
}
