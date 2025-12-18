<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create the pivot table
        Schema::create('content_block_page', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_block_id')->constrained()->onDelete('cascade');
            $table->foreignId('page_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });

        // Only drop if the column exists
        if (Schema::hasColumn('content_blocks', 'page_id')) {
            Schema::table('content_blocks', function (Blueprint $table) {
                $table->dropForeign(['page_id']);
                $table->dropColumn('page_id');
            });
        }
    }

    public function down()
    {
        // Only add if the column doesn't exist
        if (!Schema::hasColumn('content_blocks', 'page_id')) {
            Schema::table('content_blocks', function (Blueprint $table) {
                $table->foreignId('page_id')->nullable()->constrained();
            });
        }

        // Drop the pivot table
        Schema::dropIfExists('content_block_page');
    }
};