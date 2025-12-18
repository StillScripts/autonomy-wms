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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('organisation_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('provider_type'); // e.g., 'stripe', 'shopify'
            $table->string('status'); // pending, completed, failed, refunded
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3);
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['organisation_id', 'status']);
            $table->index(['provider_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
