<?php

namespace App\Http\Requests\Websites;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class WebsiteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // We're handling authorization in the controller
    }

    public function rules(): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'logo' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if ($value instanceof \Illuminate\Http\UploadedFile) {
                        if (!$value->isValid() || !in_array($value->getMimeType(), [
                            'image/jpeg', 'image/png', 'image/gif', 'image/jpg'
                        ])) {
                            $fail('The logo must be a valid image file.');
                        }
                    } elseif (is_string($value)) {
                        return true;
                    } elseif ($value !== null) {
                        $fail('The logo must be either an image file or a valid storage path.');
                    }
                }
            ]
        ];

        if ($this->isMethod('POST') || $this->has('domain')) {
            $rules['domain'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('websites')->ignore($this->route('website'))
            ];
        }

        return $rules;
    }
} 