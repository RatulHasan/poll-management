<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePollRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('poll'));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_active' => ['boolean'],
            'expires_at' => [
                'nullable',
                'date',
                Rule::when(fn () => $this->expires_at !== null, ['after:now']),
            ],
            'options' => ['array', 'min:2'],
            'options.*' => ['required', 'string', 'max:255', 'distinct'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.max' => 'The poll title cannot exceed 255 characters.',
            'options.required' => 'Please provide poll options.',
            'options.min' => 'Please provide at least 2 options for the poll.',
            'options.array' => 'Poll options must be provided as a list.',
            'options.*.required' => 'Poll options cannot be empty.',
            'options.*.distinct' => 'Each poll option must be unique.',
            'expires_at.after' => 'The expiration date must be in the future.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $options = $this->input('options', []);

            // If options array is empty but submitted
            if (is_array($options) && empty($options)) {
                $validator->errors()->add('options', 'Please provide at least 2 options for the poll.');
            }

            // If any option is empty string or whitespace only
            foreach ($options as $index => $option) {
                if (is_string($option) && trim($option) === '') {
                    $validator->errors()->add(
                        'options',
                        'Poll options cannot be empty. Please provide text for all options.'
                    );
                    break;
                }
            }
        });
    }
}
