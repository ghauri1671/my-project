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
        // Create the 'ads' table
        Schema::create('ads', function (Blueprint $table) {
            // Auto-incrementing primary key
            $table->id();

            // String column for the sidebar image path/filename
            // It's nullable, meaning an ad might not always have a sidebar image
            $table->string('sidebar_image')->nullable();

            // String column for the banner image path/filename
            // It's nullable, meaning an ad might not always have a banner image
            $table->string('banner_image')->nullable();

            // Adds 'created_at' and 'updated_at' timestamp columns
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the 'ads' table if it exists when rolling back the migration
        Schema::dropIfExists('ads');
    }
};

