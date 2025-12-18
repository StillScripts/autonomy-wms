<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('content_block_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->foreignId('organisation_id')->nullable()->constrained()->onDelete('cascade');
            $table->unique(['slug', 'organisation_id']);
            $table->json('fields')->comment('JSON schema defining the fields for this block type');
            $table->boolean('is_default')->default(false)->comment('Whether this is a default block type that comes with the application');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_block_types');
    }
}; 