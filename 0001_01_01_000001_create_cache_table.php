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
        Schema::create('signals', function (Blueprint $table) {
            $table->id();
            $table->string('pair_name');
            $table->decimal('entry_price', 10, 5); // Precision for forex prices
            $table->decimal('stop_loss', 10, 5);
            $table->decimal('take_profit', 10, 5); // Changed from take_profit_1
            $table->boolean('is_open')->default(true); // Default to true when created
            $table->string('group_type'); // 'free', 'premium', 'both'
            $table->enum('market_type', ['crypto', 'forex', 'stocks','indices','commodities']);
            $table->enum('signal_type', ['buy', 'sell']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signals');
    }
};
