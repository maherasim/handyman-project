<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ServiceRequest extends FormRequest
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
            'name'                           => 'required|unique:services,name,'.$id,
            'category_id'                    => 'required|exists:categories,id',
            'subcategory_id'                 => 'required|exists:sub_categories,id',
            'country_id'                     => 'required|exists:countries,id',
            'state_id'                       => 'required|exists:states,id',
            'city_id'                        => 'required|exists:cities,id',
            'type'                           => 'required|in:fixed,hourly,Daily',
            'price'                          => 'required|numeric|min:0',
            'minimum_booking'                 => 'required|numeric|min:0',
            'duration'                       => ['required', 'regex:/^(\d+(\.\d+)?|\d+:(?:[0-5]\d|[0-9]))$/'],
            'status'                         => 'required|in:0,1',
            'visit_type'                     => 'required|string|max:50',
            'remote_work_level'              => 'required|in:onsite,25_remote,50_remote,75_remote,100_remote',
            'career_level'                   => 'required|in:intern,entry,junior,mid,senior,lead,manager',
            'travel_required'                => 'required|in:0,1',
            'description'                    => 'required|string',
            'cancellation_policy'            => 'required|string',
            'provider_address_id'            => 'required|array|min:1',
            'provider_address_id.*'           => 'required',
            'discount'                       => 'nullable|numeric|min:0|max:99',
        ];

        if ($this->has('provider_id') && $this->filled('provider_id')) {
            $rules['provider_id'] = 'required|exists:users,id';
        }

        // Require at least one attachment when creating via web (non-API)
        if (empty($id) && !$this->is('api/*')) {
            $rules['service_attachment'] = 'required';
        }

        // Advance payment amount required when advance payment is enabled
        if ($this->boolean('is_enable_advance_payment')) {
            $rules['advance_payment_amount'] = 'required|numeric|min:1|max:99';
        }

        return $rules;
    }
    public function messages()
    {
        return [
            'duration.regex' => 'Duration must be a number (hours, e.g. 40 or 48) or HH:MM (e.g. 46:30). Do not enter text like "2 days".',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        if ( request()->is('api*')){
            $data = [
                'status' => 'false',
                'message' => $validator->errors()->first(),
                'all_message' =>  $validator->errors()
            ];

            throw new HttpResponseException(response()->json($data,422));
        }

        throw new HttpResponseException(redirect()->back()->withInput()->with('errors', $validator->errors()));
    }
}
