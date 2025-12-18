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
        Schema::create('stripe_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->string('stripe_payment_intent_id')->unique();
            $table->string('stripe_payment_method_id')->nullable();
            $table->string('stripe_customer_id')->nullable();
            $table->string('stripe_environment');
            $table->json('stripe_metadata')->nullable();
            $table->timestamps();

            $table->index('stripe_payment_intent_id');
            $table->index('stripe_customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_payments');
    }
};
