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
        Schema::table('business_modules', function (Blueprint $table) {
            $table->dropColumn('settings');
        });
        
        Schema::table('business_modules', function (Blueprint $table) {
            $table->text('settings')->nullable()->after('enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('business_modules', function (Blueprint $table) {
            $table->dropColumn('settings');
        });
        
        Schema::table('business_modules', function (Blueprint $table) {
            $table->json('settings')->nullable()->after('enabled');
        });
    }
};
