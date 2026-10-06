<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\DeliveryChallan;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<DeliveryChallan> */
class DeliveryChallanFactory extends Factory
{
    public function definition(): array
    {
        return [
            'client_id' => Client::factory(),
            'challan_number' => 'DC-'.fake()->unique()->numerify('######'),
            'issued_on' => now()->toDateString(),
        ];
    }
}
