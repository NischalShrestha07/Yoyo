<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Variant SKUs are scanned/entered at the business level, so the unique
 * constraint belongs on (business_id, sku) rather than (product_id, sku).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropUnique(['product_id', 'sku']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->unique(['business_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::table('product_variants', function (Blueprint $table) {
            $table->dropUnique(['business_id', 'sku']);
        });

        Schema::table('product_variants', function (Blueprint $table) {
            $table->unique(['product_id', 'sku']);
        });
    }
};
