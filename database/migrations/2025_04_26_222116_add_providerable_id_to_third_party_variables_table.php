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
            $table->foreignId('third_party_providerable_id')
                  ->nullable()
                  ->constrained('third_party_providerables')
                  ->onDelete('cascade');
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
