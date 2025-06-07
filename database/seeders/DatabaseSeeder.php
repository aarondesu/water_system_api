<?php
namespace Database\Seeders;

use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\Subscriber;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(100)->create();
        User::factory()->create([
            'first_name' => 'Test User',
            'username'   => 'admin',
        ]);

        Subscriber::factory(100)->create();
        Meter::factory(100)->create();
        MeterReading::factory(300)->create();
    }
}
