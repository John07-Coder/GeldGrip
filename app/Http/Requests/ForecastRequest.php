<?php

namespace App\Http\Requests;

class ForecastRequest extends MonthRequest
{
    protected function prepareForValidation(): void
    {
        foreach (['income', 'fixed', 'variable'] as $field) {
            if (is_string($this->input($field))) {
                $this->merge([$field => str_replace(',', '.', trim($this->input($field)))]);
            }
        }
    }

    public function rules(): array
    {
        $rules = parent::rules() + ['horizon' => ['nullable', 'integer', 'in:3,6,12']];
        foreach (['income', 'fixed', 'variable'] as $field) {
            $rules[$field] = ['bail', 'nullable', 'numeric', 'min:0', 'max:9999999999.99', 'regex:/^\d{1,10}(?:\.\d{1,2})?$/D'];
        }

        return $rules;
    }
}
