<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

class MemberImportRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true; // Authorization is handled by the controller's middleware
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:csv,txt,xls,xlsx,json',
                'max:10240', // 10MB max
            ],
            'update_existing' => [
                'boolean',
            ],
            'mapping' => [
                'array',
            ],
            'mapping.*' => [
                'string',
                'nullable',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'file.required' => 'Please select a file to import.',
            'file.file' => 'The uploaded file is not valid.',
            'file.mimes' => 'The file must be a CSV, Excel, or JSON file.',
            'file.max' => 'The file may not be greater than 10MB.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation()
    {
        // Convert 'true'/'false' strings to boolean for update_existing
        if ($this->has('update_existing')) {
            $this->merge([
                'update_existing' => filter_var($this->update_existing, FILTER_VALIDATE_BOOLEAN),
            ]);
        }
    }
}
