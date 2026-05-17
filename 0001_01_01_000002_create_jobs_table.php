<?php

namespace Database\Seeders;

use Carbon\Carbon;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AssetSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $forexPairs = [
            [
                'pair_name' => 'EUR/USD',
                'market_type' => 'forex',
                'image' => 'images/forex/eurusd.png',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pair_name' => 'GBP/USD',
                'market_type' => 'forex',
                'image' => 'images/forex/gbpusd.png',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pair_name' => 'USD/JPY',
                'market_type' => 'forex',
                'image' => 'images/forex/usdjpy.png',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pair_name' => 'USD/CHF',
                'market_type' => 'forex',
                'image' => 'images/forex/usdchf.png',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'pair_name' => 'AUD/USD',
                'market_type' => 'forex',
                'image' => 'images/forex/audusd.png',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ];

        foreach ($forexPairs as $pair) {
            DB::table('assets')->insert($pair);
        }
    }
}
