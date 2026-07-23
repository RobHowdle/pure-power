<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            [
                'email' => 'robhowdle94@gmail.com',
            ],
            [
                'name' => 'Admin',
                'password' => Hash::make('!B3_CfptaZEuv-P4-fRY6Uie'),
            ]
        );

        $user->assignRole('super-admin');
    }
}