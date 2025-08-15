<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the formulas table
        Schema::create('formulas', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('expression');
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('formula_variables', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('description')->nullable();
            $table->double('value');
            $table->string('unit')->nullable();                                            // e.g., 'liters', 'kilograms', etc.
            $table->boolean('is_required')->default(true);                                 // Whether variable is required
            $table->foreignId('formula_id')->constrained('formulas')->onDelete('cascade'); // Foreign key to formulas table
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('formula_variables');
        Schema::dropIfExists('formulas');
    }
};
