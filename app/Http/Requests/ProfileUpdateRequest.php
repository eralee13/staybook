<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation cancellations that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['string', 'max:255'],
            'email' => ['email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'phone' => ['string', 'max:255'],
            'comission' => ['string', 'max:255'],
            'bank_name' => ['string', 'max:255'],
            'bank_inn' => ['string', 'max:255'],
            'bank_account' => ['string', 'max:255'],
            'bank_bic' => ['string', 'max:255'],
            'address' => ['string', 'max:255'],
        ];
    }
}
