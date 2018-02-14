<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddLedgerTransactionFormRequest extends FormRequest
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
            'entries' => 'required',
            'entries.*.ledger_id' => 'required|numeric',
            'entries.*.dr' => 'required',
            'entries.*.cr' => 'required',
        ];
    }

    public function messages()
    {
        return [
            'entries.required' => 'Please add entries to post to the General Ledger',
            'entries.*.ledger_id.required' => 'Please select a ledger for the entry',
            'entries.*.ledger_id.numeric' => 'Invalid ledger selected for the entry',
            'entries.*.dr.required' => 'Please provide a value for the Debit entry. 0 is an accepted value.',
            'entries.*.dr.numeric' => 'The value entered for Debit entry must be a number. 0 is an accepted value.',
            'entries.*.cr.required' => 'Please provide a value for the Credit entry. 0 is an accepted value.',
            'entries.*.cr.numeric' => 'The value entered for Debit entry must be a number. 0 is an accepted value.',
        ];
    }
}
