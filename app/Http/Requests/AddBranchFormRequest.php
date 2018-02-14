<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddBranchFormRequest extends FormRequest
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
            'name' => 'required|unique:branches',
            'location' => '',
            'code' => ''
        ];
    }

    public function messages()
    {
        return [
            'name.required' => 'The name of the branch is required',
            'name.unique' => 'There is already a branch with that name'
        ];
    }
}
