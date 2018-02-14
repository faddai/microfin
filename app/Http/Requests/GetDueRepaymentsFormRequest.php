<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class GetDueRepaymentsFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'startDate' => 'date',
            'endDate' => 'date',
            'credit_officer' => 'numeric|exists:users,id'
        ];
    }
}
