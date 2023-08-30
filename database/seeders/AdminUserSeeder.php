<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = [
            'uuid' => Str::orderedUuid(),
            'first_name' => 'Admin',
            'last_name' => 'Lr',
            'email' => "admin@buckhill.co.uk",
            'email_verified_at' => now(),
            'is_admin' => 1,
            'password' => Hash::make("admin"),
            'avatar' => Str::orderedUuid(),
            'address' => "Building Number 376,Boyer Glen, 41725 Matt Pines, Wisconsin, Careyberg N3Y0N1",
            'phone_number' => "8520147963",
        ];

        User::create($admin);
    }
}
