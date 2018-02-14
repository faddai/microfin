<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DepositFormRequest extends FormRequest
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
            'client_id' => 'bail|required|numeric|exists:clients,id',
            'ledger_id' => 'bail|required|exists:ledgers,id',
            'cr' => 'sometimes|bail|required|numeric',
            'dr' => 'sometimes|bail|required|numeric',
            'narration' => ''
        ];
    }

    public function messages()
    {
        return [
            'client_id.required' => 'You must select a Client',
            'client_id.exists' => 'The Client you selected could not be found',
            'cr.required' => 'You must enter an Amount',
            'cr.min' => 'The Amount cannot be less than 1',
            'dr.required' => 'You must enter an Amount',
            'dr.min' => 'The Amount cannot be less than 1',
            'ledger_id.required' => 'You must select a Ledger to post the transaction to',
            'ledger_id.exists' => 'The Ledger you selected could not be found.',
        ];
    }
}
