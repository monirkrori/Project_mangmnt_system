<?php

namespace App\Http\Requests\V1\Task;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTaskRequest extends FormRequest
{
 /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('task'));
    }

    /**
     * Ensure 'status' and 'priority' are lowercase to match DB
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
     * Get the validation rules that apply to the request
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'project_id'   => ['sometimes' , 'exists:projects,id'],
            'assigned_to'  => ['sometimes' , 'exists:users,id'],
            'name'         => ['sometimes' , 'string', 'max:255'],
            'description'  => ['nullable'  , 'string'],
            'status'       => ['sometimes' , 'in:pending,in_progress,completed'],
            'priority'     => ['sometimes' , 'in:low,medium,high'],
            'due_date'     => ['nullable'  , 'date'],
        ];
    }


    /**
     * clear messages for rules
     */
    public function messages(): array
    {
        return [
            'project_id.exists'   => 'The selected project does not exist.',
            'assigned_to.exists'  => 'The selected user does not exist.',
            'name.string'         => 'Task name must be a valid string.',
            'status.in'           => 'Invalid status provided.',
            'priority.in'         => 'Invalid priority value.',
            'due_date.date'       => 'Due date must be a valid date.',
        ];
    }
}
