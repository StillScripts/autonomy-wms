<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->foreignId('organisation_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('website_id')->nullable()->constrained()->onDelete('cascade');
            
            // Add a compound index for better query performance
            $table->index(['organisation_id', 'website_id']);
        });
    }

    public function down()
    {
        Schema::table('content_blocks', function (Blueprint $table) {
            $table->dropIndex(['organisation_id', 'website_id']);
            $table->dropForeign(['website_id']);
            $table->dropForeign(['organisation_id']);
            $table->dropColumn(['organisation_id', 'website_id']);
        });
    }
}; 