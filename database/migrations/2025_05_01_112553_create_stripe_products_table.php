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
        Schema::create('stripe_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('stripe_id')->unique(); // Stripe's product ID
            $table->string('stripe_price_id')->unique(); // Stripe's price ID
            $table->string('stripe_environment'); // 'test' or 'live'
            $table->json('stripe_metadata')->nullable(); // Raw Stripe product data
            $table->timestamps();
            
            $table->index('stripe_id');
            $table->index('stripe_price_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stripe_products');
    }
};
