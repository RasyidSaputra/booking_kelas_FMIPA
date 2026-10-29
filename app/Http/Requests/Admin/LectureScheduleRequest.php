<?php

namespace App\Http\Requests\Admin;

use App\Support\LectureScheduleRules;

class LectureScheduleRequest extends MasterRequest
{
    public function rules(): array
    {
        return LectureScheduleRules::rules($this->isMethod('PATCH'));
    }

    public function after(): array
    {
        return [LectureScheduleRules::validatePeriod(...)];
    }

    public function attributes(): array
    {
        return LectureScheduleRules::attributes() + parent::attributes();
    }

    public function messages(): array
    {
        return LectureScheduleRules::messages() + parent::messages();
    }
}
