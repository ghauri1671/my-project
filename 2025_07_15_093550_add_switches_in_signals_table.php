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
        Schema::table('users', function (Blueprint $table) {
            // Add after 'email' if you want ordering (MySQL only; safe to keep)
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'subscriber'])
                    ->default('subscriber')
                    ->after('email');
            }
            $table->string('raw')->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'role')) {
                $table->dropColumn('role');
            }
            $table->dropColumn('raw');

        });
    }
};
