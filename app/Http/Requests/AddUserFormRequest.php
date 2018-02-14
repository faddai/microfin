<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddUserFormRequest extends FormRequest
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
     * NB: sometimes is applied because some of the requests don't
     * post every data required. For example, when suspending a
     * user account
     *
     * todo Add a Controller to manage User account suspension since it is sidestepping roles.required during update
     *
     * @return array
     */
    public function rules()
    {
        $isUpdate = request()->route()->hasParameter('user');

        return [
            'name' => 'sometimes|required|max:255',
            'email' => $isUpdate ? 'sometimes|required|email|max:255' : 'required|email|max:255|unique:users',
            'password' => 'sometimes|required|min:8|confirmed',
            'branch_id' => 'sometimes|required',
            'roles' => $isUpdate ? 'sometimes|array' : 'array|required',
            'roles.*' => $isUpdate ? 'sometimes|numeric' : 'numeric'
        ];
    }

    public function messages()
    {
        return [
            'branch_id.required' => 'You must assign a user to a branch',
            'roles.required' => 'You must assign at least 1 role to the user',
        ];
    }
}
