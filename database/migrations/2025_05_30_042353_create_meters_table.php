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
        Schema::create('meters', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("subscriber_id")->unsigned()->nullable()->unique();
            $table->integer("number")->unique();
            $table->string("note")->nullable();
            $table->timestamps();
            $table->foreign('subscriber_id')->references('id')->on('subscribers');
        });

        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('meter_id')->unsigned();
            $table->integer('reading');
            $table->string("note")->nullable();
            $table->timestamps();
            $table->foreign('meter_id')->references('id')->on('meters');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meter_readings');
        Schema::dropIfExists('meters');
    }
};
