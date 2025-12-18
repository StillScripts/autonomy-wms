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
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->foreignId('content_block_type_id')->constrained('content_block_types')->onDelete('cascade');
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->string('type')->default('text');
            $table->dropForeign(['content_block_type_id']);
            $table->dropColumn('content_block_type_id');
        });
    }
};
