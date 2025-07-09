<?php
namespace Database\Seeders;

use App\Models\Meter;
use App\Models\MeterReading;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class MeterReadingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $meters = Meter::all();

        foreach ($meters as $meter) {
            $date = Carbon::parse('2025-01-01');

            for ($i = 0; $i < 6; $i++) {
                MeterReading::factory()
                    ->incrementalReading($meter, $date)
                    ->create();

                $date = $date->addMonth();
            }
        }
    }
}
