<?php

namespace App\Http\Requests;

use App\Http\Requests\Admin\MasterRequest;
use Illuminate\Validation\Rule;

class ReviewBookingRequest extends MasterRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, array>
     */
    public function rules(): array
    {
        return [
            'decision' => ['required', Rule::in(['approved', 'rejected'])],
            'rejection_reason' => ['required_if:decision,rejected', 'nullable', 'string', 'max:2000'],
        ];
    }
}
