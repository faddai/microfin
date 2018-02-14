<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class AddClientFormRequest extends FormRequest
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
        $rules = [
            'phone1' => 'required|numeric',
            'phone2' => '',
            'status' => 'numeric',
            'relationship_manager' => 'required|numeric',
            'email' => 'email',
            'address' => 'required',
            'nationality' => 'required',
            'branch_id' => 'required|numeric',
            'identification_type' => 'required|in:'. implode(',', array_keys(trans('identification_types'))),
            'identification_number' => 'required_with:identification_type'
        ];

        return array_merge(
            $rules,
            $this->getCorporateClientSpecificRules(),
            $this->getGroupClientSpecificRules(),
            $this->getIndividualClientSpecificRules()
        );
    }

    private function getCorporateClientSpecificRules()
    {
        $rules = [];

        if (request()->get('type') === 'corporate') {
            $rules['date_of_incorporation'] = 'required|date|before:'. Carbon::today()->format('F d Y');
            $rules['company_name'] = 'required';
            $rules['company_type'] = 'required|in:'. implode(',', trans('company_ownership_types'));
            $rules['business_registration_number'] = 'required';
        }

        return $rules;
    }

    private function getIndividualClientSpecificRules()
    {
        $rules = [];

        if (request()->get('type') === 'individual') {
            $rules['firstname'] = 'required';
            $rules['lastname'] = 'required';
            $rules['middlename'] = '';
            $rules['dob'] = 'required|date|before:'. Carbon::parse('18 years ago')->format('F d Y');
            $rules['gender'] = 'in:male,female';
            $rules['marital_status'] = 'in:'. implode(',', trans('marital_status'));
            $rules['spouse_name'] = 'required_if:marital_status,married';
        }

        return $rules;
    }

    private function getGroupClientSpecificRules()
    {
        $rules = [];

        if (request()->get('type') === 'group') {
            $rules[''] = '';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'firstname.required' => 'The Client\'s first name is required',
            'lastname.required' => 'The Client\'s last name is required',
            'dob.required' => 'Please provide Client\'s date of birth',
            'dob.before' => 'The Client cannot be younger than 18 years',
            'branch_id.required' => 'Please assign the Client to a branch',
            'date_of_incorporation.required' => 'Please provide the Date of Incorporation of business',
            'date_of_incorporation.date' => 'Date of Incorporation of business must be a valid date',
            'company_name.required' => 'Please provide the Company name',
            'company_type.required' => 'Please indicate the Company type',
            'business_registration_number.required' => 'Please provide the Company registration number',
            'phone1.required' => 'You must add at least 1 phone number for the Client',
            'relationship_manager.required' => 'Please assign this client to a Relationship manager',
            'identification_number.required_with' => 'You must enter the Identification number for the selected ID type',
        ];
    }
}
