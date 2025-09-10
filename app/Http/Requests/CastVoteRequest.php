<?php

namespace App\Http\Requests;

use App\Models\Poll;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CastVoteRequest extends FormRequest
{
    private ?Poll $pollInstance = null;

    protected function getPoll(): Poll
    {
        if (!$this->pollInstance) {
            $this->pollInstance = $this->route('poll');
        }
        return $this->pollInstance;
    }

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
        $poll = $this->getPoll();

        return [
            'vote' => [
                function ($attribute, $value, $fail) use ($poll) {
                    if (!$poll->is_active || ($poll->expires_at && $poll->isExpired())) {
                        $fail('This poll is not active or has expired.');
                    }
                }
            ],
            'option_id' => [
                'required',
                Rule::exists('poll_options', 'id')->where(function ($query) use ($poll) {
                    return $query->where('poll_id', $poll->id);
                }),
            ],
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'vote' => true,
        ]);
    }
}
