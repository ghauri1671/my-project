<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('signals', function (Blueprint $table) {
            // Drop existing columns to recreate them with new precision
            $table->dropColumn(['entry_price', 'stop_loss', 'take_profit']);
        });

        Schema::table('signals', function (Blueprint $table) {
            // Add new columns with increased precision
            $table->decimal('entry_price', 12, 5)->after('asset_id');
            $table->decimal('stop_loss', 12, 5)->after('entry_price');
            $table->decimal('take_profit', 12, 5)->after('stop_loss');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signals', function (Blueprint $table) {
            // Revert to original precision
            $table->dropColumn(['entry_price', 'stop_loss', 'take_profit']);
        });

        Schema::table('signals', function (Blueprint $table) {
            $table->decimal('entry_price', 12, 5)->after('asset_id');
            $table->decimal('stop_loss', 12, 5)->after('entry_price');
            $table->decimal('take_profit', 12, 5)->after('stop_loss');
        });
    }
};