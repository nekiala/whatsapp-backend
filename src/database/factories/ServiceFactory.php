<?php

namespace Database\Factories;

use App\Models\Service;
use Exception;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     * @throws Exception
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->streetName,
            'price' => random_int(500, 1000)
        ];
    }
}
