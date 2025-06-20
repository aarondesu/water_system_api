<?php
namespace Database\Factories;

use App\Models\Meter;
use App\Models\MeterReading;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

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
            'meter_id'   => null,
            'reading'    => fake()->numberBetween(100, 3000),
            'created_at' => now(),
        ];
    }

    public function incrementalReading(Meter $meter, Carbon $readingDate)
    {
        $latestReading = MeterReading::where('meter_id', $meter->id)
            ->orderByDesc('created_at')
            ->first();

        $previousValue = $latestReading->reading ?? 0;
        $newValue      = $previousValue + rand(5, 100);

        return $this->state([
            'meter_id'   => $meter->id,
            'created_at' => $readingDate,
            'reading'    => $newValue,
        ]);
    }
}
