<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->string('invoice_prefix')->nullable()->after('invoice_number');
            $table->date('due_date')->nullable()->after('completed_at');
            $table->string('payment_terms')->nullable()->after('due_date');
            $table->string('po_number')->nullable()->after('payment_terms');
            $table->text('terms_conditions')->nullable()->after('notes');
            $table->json('billing_details')->nullable()->after('terms_conditions');
        });
    }

    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropColumn(['invoice_prefix', 'due_date', 'payment_terms', 'po_number', 'terms_conditions', 'billing_details']);
        });
    }
};
