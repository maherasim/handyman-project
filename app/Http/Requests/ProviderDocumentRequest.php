<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class ProviderDocumentRequest extends FormRequest
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
        $providerId = auth()->user()->hasRole('provider') ? auth()->id() : $this->provider_id;
        
        return [
            'document_id'              => [
                'required',
                function ($attribute, $value, $fail) use ($providerId) {
                    // Check if updating existing document
                    if ($this->id) {
                        return; // Allow update
                    }
                    
                    // Check for duplicate document_id for this provider
                    $exists = \App\Models\ProviderDocument::where('provider_id', $providerId)
                        ->where('document_id', $value)
                        ->exists();
                    
                    if ($exists) {
                        $fail('This document type has already been uploaded. Please delete the existing one first or upload a different document type.');
                    }
                }
            ],
            'provider_document'        => 'mimes:jpg,jpeg,png,pdf,docx'
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
