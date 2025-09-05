<?php

namespace App\Http\Requests;

use App\Enums\TaskStages;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Task Request
 *
 * Validates task creation and update requests
 */
class TaskRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $rules = [
            'title' => ['sometimes', 'string'],
            'description' => ['sometimes', 'string'],
            'stage' => ['sometimes', 'string', 'in:'.implode(',', array_column(TaskStages::cases(), 'value'))],
            'index' => ['sometimes', 'nullable', 'integer', 'min:0'],
        ];

        // If this is a POST request (creating a new task), title and description are required
        if ($this->isMethod('POST')) {
            $rules['title'][] = 'required';
            $rules['description'][] = 'required';
        }

        return $rules;
    }

    /**
     * Determine if the user is authorized to make this request
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }
}
