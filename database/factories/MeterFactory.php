<?php
namespace Database\Factories;

use App\Models\Subscriber;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Meter>
 */
class MeterFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subscribers = Subscriber::all();
        return [
            'number'        => fake()->unique()->randomNumber(3),
            'subscriber_id' => fake()->unique()->numberBetween(1, int2: $subscribers->count()),
            'status'        => fake()->randomElement(['active', 'inactive']),
        ];
    }
}
