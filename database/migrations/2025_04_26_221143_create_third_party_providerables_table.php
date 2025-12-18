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
        Schema::create('third_party_providerables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('third_party_provider_id')->constrained()->onDelete('cascade');
            $table->morphs('providerable'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('third_party_providerables');
    }
};
