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
        Schema::table('third_party_variables', function (Blueprint $table) {
            $table->unique(['name', 'third_party_provider_id'], 'third_party_variables_name_provider_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('third_party_variables', function (Blueprint $table) {
            //
        });
    }
};
