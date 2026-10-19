<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            ['name' => 'Test User', 'password' => Hash::make('password')]
        );

        Account::firstOrCreate(
            ['account_number' => '1000000001'],
            ['user_id' => $user->id, 'balance' => 1000.00, 'minimum_balance' => 100.00]
        );
    }
}