<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);
        
        // Create a demo admin user
        $user1 = User::create([
            'name' => 'Staff Logistik',
            'username' => 'stafflogistik',
            'email' => 'stafflogistik@example.com',
            'division' => 'Logistik dan SDM',
            'password' => Hash::make('password')
        ]);
        
        $user1->assignRole('staff_logistik');

        $user2 = User::create([
            'name' => 'Kaur Laboratorium',
            'username' => 'kaurlab',
            'email' => 'kaurlab@example.com',
            'division' => 'Laboratorium',
            'password' => Hash::make('password')
        ]);
        
        $user2->assignRole('kaur_laboratorium');

        $user3 = User::create([
            'name' => 'Kaur Keuangan Logistik SDM',
            'username' => 'kaurkeulogsdm',
            'email' => 'kaurkeulogsdm@example.com',
            'division' => 'Keuangan Logistik dan SDM',
            'password' => Hash::make('password')
        ]);
        
        $user3->assignRole('kaur_keuangan_logistik_sdm');

        $user4 = User::create([
            'name' => 'Wakil Dekan 2',
            'username' => 'wadek2',
            'email' => 'wadek2@example.com',
            'division' => 'Universitas',
            'password' => Hash::make('password')
        ]);
        
        $user4->assignRole('wakil_dekan_2');

        $user5 = User::create([
            'name' => 'Staff Keuangan',
            'username' => 'staffkeu',
            'email' => 'staffkeu@example.com',
            'division' => 'Keuangan',
            'password' => Hash::make('password')
        ]);
        
        $user5->assignRole('staff_keuangan');

        $user6 = User::create([
            'name' => 'Staff Laboratorium',
            'username' => 'stafflab',
            'email' => 'stafflab@example.com',
            'division' => 'Laboratorium',
            'password' => Hash::make('password')
        ]);
        
        $user6->assignRole('staff_laboratorium');

        $this->call([
            MaintenanceSeeder::class,
            CriteriaSeeder::class,
        ]);
    }
}