<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ContentBlockType;
use Illuminate\Support\Str;

class ContentBlockTypeRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'fields' => 'required|array',
            'is_default' => 'boolean'
        ];
    }

    protected function prepareForValidation()
    {
        if (!$this->has('is_default')) {
            $this->merge(['is_default' => false]);
        }
    }
} 