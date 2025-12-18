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
        Schema::table('private_files', function (Blueprint $table) {
            $table->dropIndex('private_files_organisation_id_active_index');
            $table->dropColumn('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('private_files', function (Blueprint $table) {
            $table->boolean('active')->default(true);
            $table->index(['organisation_id', 'active']);
        });
    }
};
