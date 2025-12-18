<?php

namespace App\Http\Requests;

use App\Models\PrivateFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class PrivateFileRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization is handled in the controller
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        Log::info('[PrivateFileRequest] Validating request', [
            'method' => $this->method(),
            'has_file' => $this->hasFile('file'),
            'all_data' => $this->all(),
        ]);

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'content_type' => ['required', Rule::in(PrivateFile::CONTENT_TYPES)],
            'metadata' => ['nullable', 'array'],
            'active' => ['boolean'],
        ];

        // Only require file on creation
        if ($this->isMethod('POST')) {
            $rules['file'] = [
                'required',
                'file',
                'max:524288', // 512MB max
            ];
            
            // Log file details if present
            if ($this->hasFile('file')) {
                $file = $this->file('file');
                Log::info('[PrivateFileRequest] File validation details', [
                    'file_name' => $file->getClientOriginalName(),
                    'file_size' => $file->getSize(),
                    'file_size_mb' => round($file->getSize() / (1024 * 1024), 2),
                    'mime_type' => $file->getMimeType(),
                    'max_allowed_mb' => 512,
                    'is_within_limit' => $file->getSize() <= (524288 * 1024),
                ]);
            }
        }

        Log::info('[PrivateFileRequest] Validation rules prepared', [
            'rules' => array_keys($rules),
            'content_types_allowed' => PrivateFile::CONTENT_TYPES,
        ]);

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.max' => 'The file may not be larger than 512MB.',
            'content_type.in' => 'The selected content type is invalid.',
        ];
    }
} 