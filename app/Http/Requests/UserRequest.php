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
     * Accept legacy/alternate key "commission" for handyman registration (maps to handyman_commission).
     */
    protected function prepareForValidation(): void
    {
        if ($this->input('user_type') === 'handyman' && ! $this->filled('handyman_commission') && $this->filled('commission')) {
            $this->merge([
                'handyman_commission' => $this->input('commission'),
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $id = request()->id;
        $passwordRules = [
            'required',
            'string',
            'min:12',
            'regex:/[A-Za-z]/',
            'regex:/[0-9]/',
        ];
        $rules = [
                'username'          => 'required|max:255|unique:users,username,'.$id,
                'email'             => 'required|email|max:255|unique:users,email,'.$id,
                'contact_number'    => 'nullable', //unique:users,contact_number,'.$id,
                'profile_image'     => 'nullable|mimetypes:image/jpeg,image/png,image/jpg,image/gif,image/webp|max:4096',
                'handyman_commission' => 'nullable|numeric|min:1|max:99',
        ];

        $vatNumberRule = request()->is('api*')
            ? 'nullable|string|max:255'
            : 'required|string|max:255';

        if (request()->user_type === 'handyman') {
            $rules['first_name'] = 'required|string|max:255';
            $rules['last_name'] = 'required|string|max:255';
            $rules['contact_number'] = 'required';
            $rules['status'] = 'required|in:0,1';
            $rules['vat_number'] = $vatNumberRule;
            $rules['handyman_commission'] = 'required|numeric|min:1|max:99';
            if (request()->is('api*')) {
                $rules['languages'] = 'nullable|array';
                $rules['languages.*'] = 'string';
            } else {
                $rules['languages'] = 'required|array|min:1';
                $rules['languages.*'] = 'string';
            }
            $rules['service_address_id'] = request()->is('api*')
                ? 'nullable|exists:provider_address_mappings,id'
                : 'required|exists:provider_address_mappings,id';
            if (request()->is('api*')) {
                $rules['country_id'] = 'nullable|exists:countries,id';
                $rules['state_id'] = 'nullable|exists:states,id';
                $rules['city_id'] = 'nullable|exists:cities,id';
            } else {
                $rules['country_id'] = 'required|exists:countries,id';
                $rules['state_id'] = 'required|exists:states,id';
                $rules['city_id'] = 'required|exists:cities,id';
            }
            if (empty($id)) {
                $rules['password'] = $passwordRules;
            }
        }

        if (request()->user_type === 'provider') {
            $rules['vat_number'] = $vatNumberRule;
            if (empty($id)) {
                $rules['password'] = $passwordRules;
            }
        }

        // Profile form (setting/profile_form) – VAT Number required only for provider & handyman; optional for customers
        if (request()->has('profile') && request()->profile === 'profile') {
            if (request()->is('api*')) {
                $rules['country_id'] = 'nullable|exists:countries,id';
                $rules['state_id'] = 'nullable|exists:states,id';
                $rules['city_id'] = 'nullable|exists:cities,id';
            } else {
                $rules['country_id'] = 'required|exists:countries,id';
                $rules['state_id'] = 'required|exists:states,id';
                $rules['city_id'] = 'required|exists:cities,id';
            }
            $rules['company_name'] = 'nullable|string|max:255';
            $userType = auth()->user()->user_type ?? request()->user_type ?? null;
            if (request()->is('api*')) {
                $rules['vat_number'] = 'nullable|string|max:255';
            } else {
                $rules['vat_number'] = in_array($userType, ['provider', 'handyman'], true) ? 'required|string|max:255' : 'nullable|string|max:255';
            }
            if ($userType === 'handyman') {
                $rules['service_address_id'] = request()->is('api*')
                    ? 'nullable|exists:provider_address_mappings,id'
                    : 'required|exists:provider_address_mappings,id';
            } else {
                $rules['service_address_id'] = 'nullable|exists:provider_address_mappings,id';
            }
            $rules['skills'] = 'nullable|string|max:500';
            $rules['education'] = 'nullable|string|max:100';
            $rules['career_level'] = request()->is('api*')
                ? 'nullable|string|max:100'
                : 'required|string|max:100';
            $rules['availability'] = 'nullable|in:full_time,part_time';
            $rules['experience'] = 'nullable|string';
            if (request()->is('api*')) {
                $rules['languages'] = 'nullable|array';
                $rules['languages.*'] = 'string';
            } else {
                $rules['languages'] = 'required|array|min:1';
                $rules['languages.*'] = 'string';
            }
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
           'education.required' => __('Education') . ' ' . __('messages.is_required'),
           'career_level.required' => __('Career Level') . ' ' . __('messages.is_required'),
           'availability.required' => __('Availability') . ' ' . __('messages.is_required'),
           'experience.required' => __('Experience') . ' ' . __('messages.is_required'),
           'languages.required' => __('messages.select_name', ['select' => __('Language')]),
           'password.min' => __('auth.password_rule_min'),
           'password.regex' => __('auth.password_requirements_intro') . ' ' . __('auth.password_rule_letter') . ', ' . __('auth.password_rule_number'),
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
