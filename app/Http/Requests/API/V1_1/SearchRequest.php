<?php

namespace App\Http\Requests\API\V1_1;

use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'check_in'   => ['required','date_format:Y-m-d'],
            'check_out'  => ['required','date_format:Y-m-d','after:check_in'],
            'residency'  => ['required','string','size:2'],

            'hotel_ids'   => ['nullable','array'],
            'hotel_ids.*' => ['string'],

            'guests_groups'               => ['required','array','min:1'],
            'guests_groups.*.adults'      => ['required','integer','min:1'],
            'guests_groups.*.children_ages' => ['nullable','array'],
            'guests_groups.*.children_ages.*' => ['integer','min:0'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $data = $this->all();

        // hotel_ids может прийти как JSON-строка или CSV "14,15"
        if (isset($data['hotel_ids']) && !is_array($data['hotel_ids'])) {
            $decoded = json_decode($data['hotel_ids'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data['hotel_ids'] = $decoded;
            } else {
                $data['hotel_ids'] = array_values(array_filter(array_map('trim', explode(',', (string)$data['hotel_ids']))));
            }
        }

        // guests_groups может прийти строкой — пробуем распарсить JSON
        if (isset($data['guests_groups']) && !is_array($data['guests_groups'])) {
            $decoded = json_decode($data['guests_groups'], true);
            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                $data['guests_groups'] = $decoded;
            }
        }

        $this->replace($data);
    }

    public function messages(): array
    {
        return [
            'guests_groups.required' => 'Поле guests_groups обязательно и должно быть массивом групп гостей.',
            'guests_groups.array'    => 'guests_groups должен быть массивом. Передайте JSON, например: [{"adults":2,"children_ages":[5,8]}].',
            'guests_groups.*.adults.required' => 'У каждой группы гостей укажите количество взрослых (adults).',
            'guests_groups.*.adults.integer'  => 'adults должен быть целым числом.',
            'guests_groups.*.children_ages.array' => 'children_ages должен быть массивом чисел.',
        ];
    }
}