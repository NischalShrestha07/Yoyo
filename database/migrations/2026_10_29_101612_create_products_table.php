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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('unit_of_measurement_id')->nullable()->constrained('units_of_measurement')->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('sku')->comment('Product code');
            $table->string('barcode')->nullable();
            $table->text('description')->nullable();
            $table->enum('type', ['physical', 'composite', 'ingredient'])->default('physical');
            $table->decimal('purchase_cost', 15, 2)->default(0);
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->boolean('track_inventory')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('has_variants')->default(false);
            $table->boolean('allow_backorder')->default(false);
            $table->json('images')->nullable();
            $table->json('attributes')->nullable()->comment('Custom product attributes');
            $table->integer('min_stock_level')->default(0);
            $table->integer('max_stock_level')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['business_id', 'sku']);
            $table->unique(['business_id', 'barcode']);
            $table->index(['business_id', 'is_active']);
            $table->index(['business_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
