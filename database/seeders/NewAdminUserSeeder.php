<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NewAdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $email = "mindworth@gmail.com";
        $password = "leuterio888999";

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
