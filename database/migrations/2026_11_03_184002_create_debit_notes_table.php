<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debit_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_id')->constrained()->cascadeOnDelete();
            $table->foreignId('bill_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('debit_note_number')->index();
            $table->date('date');
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('reason')->nullable();
            $table->enum('status', ['draft', 'approved', 'applied'])->default('draft');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['business_id', 'debit_note_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('debit_notes');
    }
};
