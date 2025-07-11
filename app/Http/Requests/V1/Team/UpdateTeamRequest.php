<?php

namespace App\Http\Requests\V1\Team;

use Illuminate\Foundation\Http\FormRequest;
use function App\Http\Requests\Team\user;

class UpdateTeamRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-team');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
                        'name' => 'sometimes|string|max:255|unique:teams,name,' . $this->route('team')->id,
        ];
    }
}
