<?php

namespace App\Http\Requests\V1\Attachment;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreAttachmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => [
                'required',
                File::types([
                    // Documents
                    'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
                    // Images
                    'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp',
                    // Archives
                    'zip', 'rar', '7z',
                    // Text
                    'txt', 'csv', 'json',
                    // Code
                    'php', 'js', 'css', 'html', 'xml'
                ])->max(25 * 1024), // 25MB
            ],

            'disk' => [
                'nullable',
                'string',
                'in:public,private'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'A file is required for upload',
            'file.mimes' => 'The file type is not allowed',
            'file.max' => 'The file must not exceed 25MB',

            'disk.in' => 'The selected disk is invalid. Only "public" or "private" are allowed.',
        ];
    }
}
