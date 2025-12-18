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
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            // PostgreSQL: convert bigint to varchar
            DB::statement('ALTER TABLE products ALTER COLUMN provider_product_id TYPE VARCHAR(255) USING provider_product_id::VARCHAR;');
        } else {
            // SQLite and others: use schema builder
            Schema::table('products', function (Blueprint $table) {
                $table->string('provider_product_id', 255)->change();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'pgsql') {
            // PostgreSQL: convert varchar back to bigint (may fail if data is not integer)
            DB::statement('ALTER TABLE products ALTER COLUMN provider_product_id TYPE BIGINT USING provider_product_id::BIGINT;');
        } else {
            // SQLite and others: use schema builder
            Schema::table('products', function (Blueprint $table) {
                $table->integer('provider_product_id')->change();
            });
        }
    }
};
