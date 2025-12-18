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
        // First, remove the existing unique constraint on slug
        Schema::table('pages', function (Blueprint $table) {
            $table->dropUnique(['slug']);
        });

        // Then add a new unique constraint that combines website_id and slug
        Schema::table('pages', function (Blueprint $table) {
            $table->unique(['website_id', 'slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove the website_id + slug unique constraint
        Schema::table('pages', function (Blueprint $table) {
            $table->dropUnique(['website_id', 'slug']);
        });

        // Restore the original slug unique constraint
        Schema::table('pages', function (Blueprint $table) {
            $table->unique(['slug']);
        });
    }
};
