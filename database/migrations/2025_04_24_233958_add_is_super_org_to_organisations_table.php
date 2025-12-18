<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->boolean('is_super_org')->default(false);
        });
        
        DB::statement('CREATE UNIQUE INDEX unique_super_org ON organisations ((is_super_org IS TRUE)) WHERE is_super_org IS TRUE');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisations', function (Blueprint $table) {
            $table->dropIndex('unique_super_org');
            $table->dropColumn('is_super_org');
        });
    }
};
