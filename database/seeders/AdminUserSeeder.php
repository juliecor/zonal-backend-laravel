<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@local.test');
        $password = env('ADMIN_PASSWORD', 'admin12345');

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'Admin',
                'password' => Hash::make($password),
                'role' => 'admin',
                'token_balance' => 0,
            ]
        );

        // Ensure admin has at least one token for testing
        if ($user->tokens()->count() === 0) {
            $user->createToken('admin')->plainTextToken;
        }
    }
}
