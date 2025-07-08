<?php

namespace App\Http\Requests\V1\Task;

use Illuminate\Foundation\Http\FormRequest;

class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('create-task');
    }


    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id'   => ['required', 'exists:projects,id'],
            'assigned_to_user_id'  => ['required', 'exists:users,id'],
            'name'         => ['required', 'string', 'max:255'],
            'description'  => ['nullable', 'string'],
            'status'       => ['nullable', 'in:pending,in_progress,completed'],
            'priority'     => ['required', 'in:low,medium,high'],
            'due_date'     => ['nullable', 'date'],

        ];

    }

    /**
     * Ensure 'status' and 'priority' are lowercase to match DB
     * and assign the id of the auth user to the creator
     */
    protected function prepareForValidation(): void
    {
        $data = [];

        // Ensure status exists before converting to lowercase
        if ($this->has('status') && $this->status !== null) {
            $data['status'] = strtolower($this->status);
        }

        // Ensure priority exists before converting to lowercase
        if ($this->has('priority') && $this->priority !== null) {
            $data['priority'] = strtolower($this->priority);
        }

        $this->merge($data);
    }


    /**
     * clear messages for rules
     */
    public function messages(): array
    {
        return [
            'project_id.required' => 'The project ID is required',
            'project_id.exists'   => 'The selected project does not exist',
            'assigned_to.exists'  => 'The selected user does not exis',
            'name.required'       => 'Task name is required',
            'status.required'     => 'Status is required',
            'status.in'           => 'Status must be one of: pending, in_progress, completed',
            'priority.required'   => 'Priority is required.',
            'priority.in'         => 'Priority must be one of: low, medium, high',
            'due_date.date'       => 'Due date must be a valid date',
        ];
    }
}
