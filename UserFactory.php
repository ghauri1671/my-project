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
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->date('entry_date');
            $table->string('coin');
            $table->string('rr')->nullable(); // Risk/Reward
            $table->float('take_profit_percentage')->nullable();
            $table->float('stoploss_percentage')->nullable();
            $table->boolean('completed')->default(false);
            $table->boolean('incompleted')->default(false);
            $table->decimal('profit_loss', 10, 2)->default(0.00);
            $table->decimal('total_balance', 10, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};