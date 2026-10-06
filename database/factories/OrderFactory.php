<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Order> */
class OrderFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'reference' => null,
            'notes' => null,
        ];
    }

    public function unassigned(): static
    {
        return $this->state(['client_id' => null]);
    }
}
