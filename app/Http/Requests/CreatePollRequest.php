<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreatePollRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'expires_at' => ['nullable', 'date', 'after:now'],
            'options' => ['required', 'array', 'min:2'],
            'options.*' => ['required', 'string', 'max:255', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'options.min' => 'A poll must have at least 2 options.',
            'options.*.distinct' => 'Poll options must be unique.',
            'expires_at.after' => 'The expiration date must be in the future.',
        ];
    }
}
