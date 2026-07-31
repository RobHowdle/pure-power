<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            [
                'email' => 'admin@pure-power.uk',
            ],
            [
                'name' => 'Admin',
                'password' => Hash::make('!B3_CfptaZEuv-P4-fRY6Uie'),
            ]
        );

        $user->assignRole('super-admin');
    }
}
