<?php

namespace App\Http\Requests\Admin;

use Illuminate\Validation\Rule;

class FacilityRequest extends MasterRequest
{
    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:100', Rule::unique('facilities')->ignore($this->route('facility'))]];
    }
}
