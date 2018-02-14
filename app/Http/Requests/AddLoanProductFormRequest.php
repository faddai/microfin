<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddLoanProductFormRequest extends FormRequest
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
            'code' => 'required',
            'name' => 'required',
            'min_loan_amount' => 'sometimes|bail|numeric',
            'max_loan_amount' => 'sometimes|bail|numeric',
        ];
    }

    public function messages()
    {
        return [
            'code.required' => 'The Product code is required',
            'name.required' => 'The Product name is required',
            'min_loan_amount.numeric' => 'The Minimum Loan Amount must be a number',
            'max_loan_amount.numeric' => 'The Maximum Loan Amount must be a number',
        ];
    }
}
