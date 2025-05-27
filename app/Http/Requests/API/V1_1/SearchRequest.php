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
            'hotel_ids'                      => 'required|array',
            'residency'                      => 'required|string|size:2',
            'guests_groups'                  => 'required|array|min:1',
            'guests_groups.*.adults'         => 'required|integer|min:1',
            'guests_groups.*.children_ages'  => 'array',
            'guests_groups.*.children_ages.*'=> 'integer|min:0',
            'check_in'                       => 'required|date',
            'check_out'                      => 'required|date|after:check_in',
        ];
    }
}
