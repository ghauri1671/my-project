<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class SubscriberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $subscribers = [];

        for ($i = 1; $i <= 10; $i++) {
            $username = 'user_' . $i;
            $email = "user{$i}@example.com";

            $subscribers[] = [
                'username' => $username,
                'email' => $email,
                'password' => 'password',
                'expire_date' => Carbon::now()->addDays(rand(15, 90)),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('subscribers')->insert($subscribers);
    }
}
