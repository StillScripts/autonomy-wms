<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ContentBlock;
use Illuminate\Support\Str;

class ContentBlockRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'website_id' => 'nullable|exists:websites,id',
            'content_block_type_id' => 'required|exists:content_block_types,id',
            'content' => 'required|array',
            'description' => 'nullable|string',
        ];
    }
} 