<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.po_number' => ['required', 'string', 'max:255'],
            'lines.*.notes' => ['nullable', 'string'],
            'lines.*.item_name' => ['required', 'string', 'max:255'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'lines.*.po_pdf' => ['nullable', 'file', 'mimes:pdf', 'max:12288'],
        ];
    }
}
