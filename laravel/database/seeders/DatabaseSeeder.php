<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'admin@kantin.com'],
            [
                'name' => 'Admin Kantin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'school' => 'SMKN 1 CIOMAS',
            ]
        );

        User::firstOrCreate(
            ['email' => 'superadmin@kantin.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'school' => 'SMKN 1 CIOMAS',
            ]
        );

        User::firstOrCreate(
            ['email' => 'budi@sekolah.com'],
            [
                'name' => 'Budi Santoso',
                'username' => 'budi',
                'password' => Hash::make('password'),
                'role' => 'student',
                'school' => 'SMKN 1 CIOMAS',
                'class_name' => 'XII RPL 1',
                'nisn' => '0089234567',
                'is_present' => true,
                'balance' => 50000,
            ]
        );

        User::firstOrCreate(
            ['email' => 'budi@smkn1ciomas.sch.id'],
            [
                'name' => 'Budi Santoso',
                'username' => 'budi_ciomas',
                'password' => Hash::make('password'),
                'role' => 'student',
                'school' => 'SMKN 1 CIOMAS',
                'class_name' => 'XII RPL 1',
                'nisn' => '0089234567',
                'is_present' => true,
                'balance' => 50000,
            ]
        );

        User::firstOrCreate(
            ['email' => 'ahmad@smkn1ciomas.sch.id'],
            [
                'name' => 'Ahmad Fauzi',
                'username' => 'ahmad',
                'password' => Hash::make('password'),
                'role' => 'student',
                'school' => 'SMKN 1 CIOMAS',
                'class_name' => 'XII TKJ 1',
                'nisn' => '0098412354',
                'is_present' => true,
                'balance' => 35000,
            ]
        );

        \App\Services\CanteenManager::initDatabase();
    }
}
