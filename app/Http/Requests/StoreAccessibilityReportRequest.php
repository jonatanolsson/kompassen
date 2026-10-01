<?php

namespace App\Http\Requests;

use App\Services\ReportOptions;
use App\Services\ReportSiteIcon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAccessibilityReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('update', $this->route('project')) ?? false;
    }

    public function rules(): array
    {
        $rules = [
            'title' => ['required', 'string', 'max:255'],
            'scope' => ['nullable', 'string'],
            'site_url' => ['nullable', 'string', Rule::in(array_keys(ReportSiteIcon::sitesFor($this->route('project'))))],
            'options' => ['sometimes', 'array:'.implode(',', array_keys(ReportOptions::DEFAULTS))],
        ];

        foreach (array_keys(ReportOptions::DEFAULTS) as $option) {
            $rules['options.'.$option] = ['sometimes', 'boolean'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'title.required' => __('Report title is required.'),
            'site_url.in' => __('Choose a website from this project before generating the report.'),
        ];
    }
}
