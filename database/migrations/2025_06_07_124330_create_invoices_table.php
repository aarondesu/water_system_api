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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('subscriber_id')->unsigned();
            $table->bigInteger('meter_id')->unsigned();
            $table->bigInteger('previous_reading_id')->unsigned();
            $table->bigInteger('current_reading_id')->unsigned();
            $table->double('consumption');
            $table->double('rate_per_unit');
            $table->double('amount_due');
            $table->enum('status', ['unpaid', 'partial', 'paid']);
            $table->dateTimeTz('due_date');
            $table->timestamps();
            $table->foreign('subscriber_id')->references('id')->on('subscribers');
            $table->foreign('meter_id')->references('id')->on('meters');
            $table->foreign('previous_reading_id')->references('id')->on('meter_readings');
            $table->foreign('current_reading_id')->references('id')->on('meter_readings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
