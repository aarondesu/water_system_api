<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DefaultFormulaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //Default formulas can be seeded here
        DB::table('formulas')->insert([
            'name'        => 'Default Formula',
            'description' => 'Default formula that just uses consumption and rate_per_unit for calculating',
            'expression'  => 'consumption * rate_per_unit',
        ]);

        // Create the default variables for the default formula
        DB::table('formula_variables')->insert([
            [
                'name'        => 'rate_per_unit',
                'description' => 'Rate charged per unit of water consumed',
                'unit'        => 'PHP',
                'value'       => 80, // Example value
                'is_required' => true,
                'formula_id'  => 1,
            ],
        ]);
    }
}
