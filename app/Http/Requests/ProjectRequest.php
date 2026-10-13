<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $project = $this->route('project');

        return [
            'vehicle_id' => ['required', Rule::exists('vehicles', 'id')->where('user_id', $this->user()->id), Rule::unique('projects', 'vehicle_id')->ignore($project?->id)],
            'title' => 'required|string|max:150', 'category' => ['required', Rule::in(['maintenance', 'performance', 'appearance', 'restoration'])],
            'status' => ['required', Rule::in(['planning', 'ongoing', 'completed'])], 'description' => 'required|string|max:5000',
            'parts' => 'nullable|string|max:2000', 'services' => 'nullable|string|max:2000', 'cost' => 'nullable|numeric|min:0|max:9999999999.99|decimal:0,2',
            'show_cost' => 'required|boolean', 'is_public' => 'required|boolean', 'workshop_id' => ['nullable', Rule::exists('workshops', 'id')->where('is_public', true)],
        ];
    }
}
