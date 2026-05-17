<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class SignalSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('signals')->insert([
            [
                'pair_name' => 'EUR/USD',
                'signal_type' => 'Buy/Long',
                'entry_price' => 1.07250,
                'stop_loss' => 1.06800,
                'take_profit' => 1.07800,
                'is_open' => true,
                'group_type' => 'free',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pair_name' => 'GBP/USD',
                'signal_type' => 'Sell/Short',
                'entry_price' => 1.28000,
                'stop_loss' => 1.28500,
                'take_profit' => 1.27000,
                'is_open' => true,
                'group_type' => 'premium',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'pair_name' => 'USD/JPY',
                'signal_type' => 'Buy/Long',
                'entry_price' => 157.500,
                'stop_loss' => 156.800,
                'take_profit' => 158.700,
                'is_open' => false,
                'group_type' => 'both',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
