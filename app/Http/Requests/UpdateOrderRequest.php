<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateOrderRequest extends FormRequest
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
            'reference' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
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

                /** @var Order $order */
                $order = $this->route('order');
                $clientId = (int) $this->input('client_id');

                if ($clientId !== (int) $order->client_id
                    && app(OrderService::class)->hasDeliveriesForOtherClient($order, $clientId)) {
                    $validator->errors()->add(
                        'client_id',
                        'Cannot change the client: challans for another client already deliver against this order.'
                    );
                }
            },
        ];
    }
}
