<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StoreClientRequest extends FormRequest
{
    /** Saved in place of a phone or address left blank. */
    public const BLANK_PLACEHOLDER = '----------';

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', $this->uniqueName()],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        foreach (['phone', 'address'] as $field) {
            if (($this->fillsMissingFields() || $this->exists($field)) && blank($this->input($field))) {
                $this->merge([$field => self::BLANK_PLACEHOLDER]);
            }
        }
    }

    /**
     * On create a field that was not sent at all also gets the placeholder;
     * on update only a field sent empty does, so an absent one keeps its stored value.
     */
    protected function fillsMissingFields(): bool
    {
        return true;
    }

    protected function uniqueName(): Unique
    {
        return Rule::unique('clients', 'name');
    }
}
