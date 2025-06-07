<?php
namespace Database\Factories;

use App\Models\Meter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MeterReading>
 */
class MeterReadingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $meters = Meter::all();
        return [
            'meter_id' => fake()->numberBetween(1, $meters->count()),
            'reading'  => fake()->numberBetween(100, 3000),
        ];
    }
}
