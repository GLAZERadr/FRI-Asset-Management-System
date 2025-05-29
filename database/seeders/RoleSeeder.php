<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles
        $staffLogistik = Role::create(['name' => 'staff_logistik']);
        $staffLab = Role::create(['name' => 'staff_laboratorium']);
        $kaurLab = Role::create(['name' => 'kaur_laboratorium']);
        $kaurKeuangan = Role::create(['name' => 'kaur_keuangan_logistik_sdm']);
        $wakilDekan = Role::create(['name' => 'wakil_dekan_2']);
        $stafKeuangan = Role::create(['name' => 'staff_keuangan']);
        
        // Create permissions
        Permission::create(['name' => 'show_maintenance_request']);
        Permission::create(['name' => 'create_maintenance_request']);
        Permission::create(['name' => 'create_criteria']);
        Permission::create(['name' => 'create_excel']);
        Permission::create(['name' => 'create_payment']);
        Permission::create(['name' => 'pay_invoice']);
        Permission::create(['name' => 'show_payment']);
        Permission::create(['name' => 'edit_payment']);
        Permission::create(['name' => 'delete_payment']);
        Permission::create(['name' => 'view_reports']);
        
        // Assign permissions to roles
        $staffLogistik->givePermissionTo([
            'show_maintenance_request',
            'create_maintenance_request',
            'create_excel',
            'create_payment',
            'show_payment',
            'edit_payment',
            'delete_payment',
        ]);

        $staffLab->givePermissionTo([
            'show_maintenance_request',
            'create_maintenance_request',
            'create_excel',
            'create_payment',
            'show_payment',
            'edit_payment',
            'delete_payment',
        ]);
        
        $kaurLab->givePermissionTo([
            'show_maintenance_request',
            'create_maintenance_request',
            'create_criteria',
        ]);
        
        $kaurKeuangan->givePermissionTo([
            'show_maintenance_request',
            'create_maintenance_request',
            'create_criteria',
        ]);
        
        $wakilDekan->givePermissionTo([
            'show_maintenance_request',
            'create_maintenance_request',
        ]);

        $stafKeuangan->givePermissionTo([
            'create_payment',
            'show_payment',
            'pay_invoice'
        ]);
    }
}