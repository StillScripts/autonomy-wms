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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('currency', 3); // ISO currency code
            $table->boolean('active')->default(true);
            $table->string('provider_type'); // e.g., 'stripe', 'shopify'
            $table->unsignedBigInteger('provider_product_id');
            $table->json('metadata')->nullable(); // For any additional provider-specific data
            $table->timestamps();
            
            // Composite index for polymorphic relationship
            $table->index(['provider_type', 'provider_product_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
