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
            // Adding new boolean columns for the premium switches
            // default(false) ensures they are false if not explicitly set
            // nullable() allows them to be null, though default(false) makes this less critical for new records
            $table->boolean('entry_price_premium')->default(false)->nullable()->after('entry_price');
            $table->boolean('stop_loss_premium')->default(false)->nullable()->after('stop_loss');
            $table->boolean('take_profit_premium')->default(false)->nullable()->after('take_profit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('signals', function (Blueprint $table) {
            // Dropping the columns if the migration is rolled back
            $table->dropColumn(['entry_price_premium', 'stop_loss_premium', 'take_profit_premium']);
        });
    }
};
