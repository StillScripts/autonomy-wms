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
        Schema::create('third_party_variable_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('third_party_providerable_id')->constrained()->onDelete('cascade');
            $table->foreignId('third_party_variable_id')->constrained()->onDelete('cascade');
            $table->text('value');
            $table->timestamps();

            $table->unique(['third_party_providerable_id', 'third_party_variable_id'], 'tpv_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('third_party_variable_values');
    }
};
