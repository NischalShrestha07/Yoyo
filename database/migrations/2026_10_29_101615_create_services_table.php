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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('code')->comment('Service code');
            $table->text('description')->nullable();
            $table->decimal('selling_price', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('attributes')->nullable()->comment('Custom service attributes');
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['business_id', 'code']);
            $table->index(['business_id', 'is_active']);
            $table->index(['business_id', 'category_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
