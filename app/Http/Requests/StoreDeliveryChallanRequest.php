<?php

namespace App\Http\Requests;

use App\Models\DeliveryChallan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreDeliveryChallanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('challan_number'))) {
            $this->merge(['challan_number' => trim($this->input('challan_number'))]);
        }
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'client_id' => ['required', 'exists:clients,id'],
            'challan_number' => ['required', 'string', 'max:50', Rule::unique('delivery_challans', 'challan_number')],
            'issued_on' => ['required', 'date'],
            'vehicle_no' => ['nullable', 'string', 'max:100'],
            'remarks' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.order_item_id' => ['required', 'exists:order_items,id'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'challan_number.required' => 'Enter the DC number.',
            'challan_number.unique' => 'This DC number already exists.',
        ];
    }

    /** @return array<int, callable> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                // The unique index is case-sensitive in SQLite, so "dc-1" would slip past "DC-1".
                $taken = DeliveryChallan::query()
                    ->whereRaw('LOWER(challan_number) = ?', [mb_strtolower($this->input('challan_number'))])
                    ->exists();

                if ($taken) {
                    $validator->errors()->add('challan_number', 'This DC number already exists.');
                }

                $ids = collect($this->input('lines'))->pluck('order_item_id');

                if ($ids->count() !== $ids->unique()->count()) {
                    $validator->errors()->add('lines', 'Each PO line can only appear once on a challan.');
                }
            },
        ];
    }
}
