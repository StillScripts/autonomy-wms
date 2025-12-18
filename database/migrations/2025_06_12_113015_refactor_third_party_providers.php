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
        // Drop old tables in correct order (dependent tables first)
        Schema::dropIfExists('third_party_variable_values');
        Schema::dropIfExists('third_party_variables');
        Schema::dropIfExists('third_party_providerables');
        Schema::dropIfExists('third_party_providers');

        // Create new table structure
        Schema::create('third_party_variable_values', function (Blueprint $table) {
            $table->id();
            $table->morphs('providerable');
            $table->string('provider');
            $table->string('variable_key');
            $table->text('value');
            $table->timestamps();

            $table->unique(['providerable_id', 'providerable_type', 'provider', 'variable_key'], 'unique_provider_variable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('third_party_variable_values');

        // Recreate old tables
        Schema::create('third_party_providers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('third_party_variables', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_secret')->default(false);
            $table->foreignId('third_party_provider_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        Schema::create('third_party_providerables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('third_party_provider_id')->constrained()->cascadeOnDelete();
            $table->morphs('providerable');
            $table->timestamps();
        });

        Schema::create('third_party_variable_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('third_party_providerable_id')->constrained()->cascadeOnDelete();
            $table->foreignId('third_party_variable_id')->constrained()->cascadeOnDelete();
            $table->text('value');
            $table->timestamps();
        });
    }
};
