<?php

namespace App\Http\Requests\V1\Comment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCommentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('comment'));
    }

    public function rules(): array
    {
        return [
            'content' => [
                'required',
                'string',
                'min:3',
                'max:1000',
                function ($attribute, $value, $fail) {
                    if (str_word_count($value) < 3) {
                        $fail('The comment must be at least 3 words.');
                    }
                }
            ]
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->comment->content === $this->input('content')) {
                $validator->errors()->add('content', 'The comment content has not changed.');
            }
        });
    }
}
