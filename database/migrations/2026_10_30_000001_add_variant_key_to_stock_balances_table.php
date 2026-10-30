<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Approved stock_balances design used a nullable product_variant_id inside the
 * unique key. Both MySQL and SQLite treat NULL values as distinct inside a
 * unique index, so two concurrent receipts for a non-variant product could
 * create duplicate balance rows (and oversell). variant_key is a non-null
 * sentinel ('' for products without variants) so the unique index actually
 * enforces one balance per product/variant/location.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('stock_balances', 'variant_key')) {
            Schema::table('stock_balances', function (Blueprint $table) {
                $table->string('variant_key', 36)->default('')->after('product_variant_id');
            });
        }

        DB::table('stock_balances')->update([
            'variant_key' => DB::raw("COALESCE(CAST(product_variant_id AS CHAR), '')"),
        ]);

        // MySQL requires a dedicated index on product_id before the composite
        // unique index that currently backs the foreign key can be dropped.
        if (! Schema::hasIndex('stock_balances', 'stock_balances_product_id_index')) {
            Schema::table('stock_balances', function (Blueprint $table) {
                $table->index('product_id');
            });
        }

        Schema::table('stock_balances', function (Blueprint $table) {
            $table->dropUnique('unique_stock_balance');
        });

        Schema::table('stock_balances', function (Blueprint $table) {
            $table->unique(['product_id', 'variant_key', 'stock_location_id'], 'unique_stock_balance');
        });
    }

    public function down(): void
    {
        Schema::table('stock_balances', function (Blueprint $table) {
            $table->dropUnique('unique_stock_balance');
        });

        Schema::table('stock_balances', function (Blueprint $table) {
            $table->unique(['product_id', 'product_variant_id', 'stock_location_id'], 'unique_stock_balance');
        });

        if (Schema::hasColumn('stock_balances', 'variant_key')) {
            Schema::table('stock_balances', function (Blueprint $table) {
                $table->dropColumn('variant_key');
            });
        }
    }
};
