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
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_variant_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('stock_location_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['receipt', 'adjustment', 'transfer_out', 'transfer_in', 'sale', 'return', 'damage', 'reversal'])->default('adjustment');
            $table->enum('reason', ['purchase', 'opening_stock', 'adjustment', 'transfer', 'sale', 'return', 'damage', 'expired', 'theft', 'other'])->default('other');
            $table->string('reference')->nullable()->comment('Reference to sale/purchase/order');
            $table->integer('quantity')->comment('Positive for increase, negative for decrease');
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->decimal('total_cost', 15, 2)->nullable();
            $table->text('notes')->nullable();
            $table->json('batch_info')->nullable()->comment('For batch/expiry tracking');
            $table->json('serial_info')->nullable()->comment('For serial number tracking');
            $table->foreignId('reverses_movement_id')->nullable()->constrained('stock_movements')->nullOnDelete();
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();
            
            $table->index(['business_id', 'product_id']);
            $table->index(['business_id', 'stock_location_id']);
            $table->index(['business_id', 'type', 'occurred_at']);
            $table->index(['business_id', 'occurred_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
