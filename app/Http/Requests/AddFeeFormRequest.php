<?php

namespace App\Http\Requests;

use App\Entities\Fee;
use Illuminate\Foundation\Http\FormRequest;

class AddFeeFormRequest extends FormRequest
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
            'name' => 'required',
            'type' => sprintf('required|in:%s,%s', Fee::FIXED, Fee::PERCENTAGE),
            'rate' => 'required_unless:type,|numeric',
            'income_ledger_id' => 'bail|required|numeric|exists:ledgers,id',
            'receivable_ledger_id' => 'bail|required_unless:is_paid_upfront,1|numeric|exists:ledgers,id',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'Fee name is required',
            'type.required' => 'Please select the type of fee',
            'rate.required_unless' => 'Please enter the value for the fee type selected',
            'income_ledger_id.required' => 'Please provide an income ledger for this fee',
            'income_ledger_id.numeric' => 'Invalid income ledger provided',
            'receivable_ledger_id.required_unless' => 'You must specify a receivable ledger unless you indicate this fee as paid upfront'
        ];
    }
}
