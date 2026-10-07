<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Unique;

class StoreClientRequest extends FormRequest
{
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
            'address' => ['required', 'string'],
        ];
    }

    protected function uniqueName(): Unique
    {
        return Rule::unique('clients', 'name');
    }
}
