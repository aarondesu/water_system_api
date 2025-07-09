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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger("subscriber_id")->unsigned();
            $table->bigInteger("invoice_id")->unsigned();
            $table->string("xendit_invoice_id");
            $table->bigInteger("amount_paid");
            $table->dateTime("payment_date");
            $table->enum("payment_method", ["manual", "bank", "ewallet", "merchant"])->nullable();
            $table->string("notes")->nullable();
            $table->timestamps();
            $table->foreign("subscriber_id")->references("id")->on("subscribers");
            $table->foreign("invoice_id")->references("id")->on("invoices");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
