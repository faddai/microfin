<?php

namespace App\Http\Requests;

use App\Entities\Loan;
use Illuminate\Foundation\Http\FormRequest;

class AddLoanFormRequest extends FormRequest
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
            'client_id' => 'required|numeric|exists:clients,id', // make sure client exists in the db
            'tenure_id' => 'required',
            'repayment_plan_id' => 'required',
            'credit_officer' => 'required',
            'purpose' => '',
            'amount' => 'required|numeric',
            'rate' => 'required',
            'zone_id' => 'required',
            'grace_period' => 'numeric|min:0',
            'loan_type_id' => 'required',
            'start_date' => 'date',
            'interest_calculation_strategy' => 'bail|in:'. implode(',', [Loan::REDUCING_BALANCE_STRATEGY, Loan::STRAIGHT_LINE_STRATEGY]),
            'loan_product_id' => 'bail|required|numeric|exists:loan_products,id',

            // guarantor details
            'guarantors.*.id' => 'sometimes|numeric',
            'guarantors.*.name' => '',
            'guarantors.*.relationship' => '',
            'guarantors.*.personal_phone' => 'numeric',
            'guarantors.*.years_known' => 'numeric',
            'guarantors.*.work_phone' => 'string',
            'guarantors.*.employer' => 'string',
            'guarantors.*.job_title' => 'string',

            // loan collateral
            'collaterals.*.label' => '',
            'collaterals.*.market_value' => '',

            // loan fees
            'fees.*.id' => 'required|exists:fees,id',
            'fees.*.rate' => 'required|numeric',
        ];
    }

    /**
     * Get the validation messages that apply to the request
     *
     * @return array
     */
    public function messages()
    {
        return [
            'client_id.required' => 'You must select the Client applying for this loan',
            'client_id.exists' => 'The selected Client does\'t exist',
            'tenure_id.required' => 'Please choose the period of the loan',
            'repayment_plan_id.required' => 'Please choose a Repayment Plan for the loan',
            'credit_officer.required' => 'Please assign a Credit Officer to the loan',
            'loan_size.required' => 'Please choose the range the loan falls in',
            'purpose.required' => 'Please indicate the purpose of the loan. This helps with its approval',
            'amount.required' => 'Please specify the amount being applied for',
            'rate.required' => 'Please specify the monthly interest rate for the loan',
            'grace_period.min' => 'The grace period can not be less than 0',
            'loan_type_id.required' => 'Please choose a loan type',
            'loan_product_id.required' => 'Please choose a loan product',

            // guarantor validation messages
            'guarantors.*.id' => 'sometimes|required|numeric',
            'guarantors.*.name.required' => 'Guarantor name is required',
            'guarantors.*.relationship.required' => 'The relationship between Client and Guarantor is required',
            'guarantors.*.personal_phone.required' => 'The contact number of the Guarantor is required',
            'guarantors.*.years_known.required' => 'Please specify the number of years Client and Guarantor have known
            themselves',

            // collateral
            'collaterals.*.label.required' => 'Please add title for at least 1 Collateral for Client',
            'collaterals.*.market_value.required' => 'Please add value for at least 1 Collateral for Client',

            // fees
            'fees.*.id.exists' => 'An invalid fee was submitted',
            'fees.*.rate.required' => 'Please specify the rate for the selected fee. You can enter 0 if no fee is being charged',
        ];
    }
}
