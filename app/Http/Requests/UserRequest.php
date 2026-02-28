<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UserRequest extends FormRequest
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
        $id = request()->id;
        $rules = [
                'username'          => 'required|max:255|unique:users,username,'.$id,
                'email'             => 'required|email|max:255|unique:users,email,'.$id,
                'contact_number'    => 'nullable', //unique:users,contact_number,'.$id,
                'profile_image'     => 'mimetypes:image/jpeg,image/png,image/jpg,image/gif',
                'handyman_commission' => 'nullable|numeric|min:1|max:85',
        ];

        if (request()->user_type === 'handyman') {
            $rules['handyman_commission'] = 'required|numeric|min:1|max:85';
            $rules['languages'] = 'required|array|min:1';
            $rules['languages.*'] = 'string';
        }

        // Profile form (setting/profile_form) – make these fields required
        if (request()->has('profile') && request()->profile === 'profile') {
            $rules['country_id'] = 'required|exists:countries,id';
            $rules['state_id'] = 'required|exists:states,id';
            $rules['city_id'] = 'required|exists:cities,id';
            $rules['company_name'] = 'required|string|max:255';
            $rules['vat_number'] = 'required|string|max:255';
            $rules['skills'] = 'required|string|max:500';
            $rules['education'] = 'required|string|max:100';
            $rules['career_level'] = 'required|string|max:100';
            $rules['availability'] = 'required|in:full_time,part_time';
            $rules['experience'] = 'required|string';
            $rules['languages'] = 'required|array|min:1';
            $rules['languages.*'] = 'string';
        }

        return $rules;
    }

    public function messages()
    {
        return [
           'profile_image.*' => __('messages.image_png_gif'),
           'country_id.required' => __('messages.select_name', ['select' => __('messages.country')]),
           'state_id.required' => __('messages.select_name', ['select' => __('messages.state')]),
           'city_id.required' => __('messages.select_name', ['select' => __('messages.city')]),
           'company_name.required' => __('Company Name') . ' ' . __('messages.is_required'),
           'vat_number.required' => __('Vat Number') . ' ' . __('messages.is_required'),
           'skills.required' => __('Skills') . ' ' . __('messages.is_required'),
           'education.required' => __('Education') . ' ' . __('messages.is_required'),
           'career_level.required' => __('Career Level') . ' ' . __('messages.is_required'),
           'availability.required' => __('Availability') . ' ' . __('messages.is_required'),
           'experience.required' => __('Experience') . ' ' . __('messages.is_required'),
           'languages.required' => __('messages.select_name', ['select' => __('Language')]),
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ( request()->is('api*')){
            $data = [
                'status' => false,
                'message' => $validator->errors()->first(),
                'all_message' =>  $validator->errors()
            ];

            throw new HttpResponseException(response()->json($data,406));
        }

        throw new HttpResponseException(redirect()->back()->withInput()->with('errors', $validator->errors()));
    }
}
