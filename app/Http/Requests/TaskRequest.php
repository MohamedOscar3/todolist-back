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
        return [
            'title' => ['required'],
            'description' => ['required'],
            'stage' => ['in:'.implode(',', array_column(TaskStages::cases(), 'value'))],
            'index' => ['nullable', 'integer', 'min:0'],
        ];
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
