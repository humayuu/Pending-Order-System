<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<OrderItem> */
class OrderItemFactory extends Factory
{
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'item_name' => fake()->word(),
            'po_number' => 'PO-'.fake()->unique()->numerify('####'),
            'quantity' => 10,
        ];
    }
}
