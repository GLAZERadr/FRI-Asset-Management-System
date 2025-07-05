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
        $staffLogistik = Role::firstOrCreate(['name' => 'staff_logistik']);
        $staffLab = Role::firstOrCreate(['name' => 'staff_laboratorium']);
        $kaurLab = Role::firstOrCreate(['name' => 'kaur_laboratorium']);
        $kaurKeuangan = Role::firstOrCreate(['name' => 'kaur_keuangan_logistik_sdm']);
        $wakilDekan = Role::firstOrCreate(['name' => 'wakil_dekan_2']);
        $stafKeuangan = Role::firstOrCreate(['name' => 'staff_keuangan']);
        
        // Create permissions
        Permission::firstOrCreate(['name' => 'show_asset']);
        Permission::firstOrCreate(['name' => 'show_monitoring_verification_report']);
        Permission::firstOrCreate(['name' => 'scan_monitoring_qr_code']);
        Permission::firstOrCreate(['name' => 'show_monitoring_report']);
        Permission::firstOrCreate(['name' => 'show_monitoring_report_validation']);
        Permission::firstOrCreate(['name' => 'verify_monitoring_report']);

        Permission::firstOrCreate(['name' => 'show_fix_verification_report']);
        Permission::firstOrCreate(['name' => 'show_fix_verification_history']);
        Permission::firstOrCreate(['name' => 'show_fix_periodic_maintenance']);
        Permission::firstOrCreate(['name' => 'show_fix_status']);
        Permission::firstOrCreate(['name' => 'show_fix_damaged_report_validation']);
        Permission::firstOrCreate(['name' => 'show_fix_damaged_report_validation_history']);
        Permission::firstOrCreate(['name' => 'show_fix_periodic_maintenance_report']);
        Permission::firstOrCreate(['name' => 'show_fix_final_report']);
        Permission::firstOrCreate(['name' => 'show_fix_report']);

        Permission::firstOrCreate(['name' => 'show_maintenance_request']);
        Permission::firstOrCreate(['name' => 'create_maintenance_request']);
        Permission::firstOrCreate(['name' => 'create_criteria']);
        Permission::firstOrCreate(['name' => 'create_excel']);
        Permission::firstOrCreate(['name' => 'create_payment']);
        Permission::firstOrCreate(['name' => 'mark_payment_as_paid']);
        Permission::firstOrCreate(['name' => 'pay_invoice']);
        Permission::firstOrCreate(['name' => 'show_payment']);
        Permission::firstOrCreate(['name' => 'edit_payment']);
        Permission::firstOrCreate(['name' => 'delete_payment']);
        Permission::firstOrCreate(['name' => 'view_reports']);
        
        // Assign permissions to roles
        $staffLogistik->givePermissionTo([
            'show_asset',
            'show_monitoring_verification_report',
            'show_fix_verification_report',
            'show_fix_verification_history',
            'show_fix_periodic_maintenance',
            'show_fix_status',
            'show_maintenance_request',
            'create_maintenance_request',
            'create_excel',
            'create_payment',
            'show_payment',
            'edit_payment',
            'delete_payment',
        ]);

        $staffLab->givePermissionTo([
            'show_asset',
            'show_fix_verification_report',
            'show_fix_verification_history',
            'show_fix_periodic_maintenance',
            'show_fix_status',
            'scan_monitoring_qr_code',
            'show_monitoring_report',
            'show_maintenance_request',
            'create_maintenance_request',
            'create_excel',
            'create_payment',
            'show_payment',
            'edit_payment',
            'delete_payment',
        ]);
        
        $kaurLab->givePermissionTo([
            'show_monitoring_report',
            'show_monitoring_report_validation',
            'show_fix_damaged_report_validation',
            'show_fix_damaged_report_validation_history',
            'show_fix_periodic_maintenance_report',
            'show_fix_final_report',
            'show_maintenance_request',
            'create_maintenance_request',
            'create_criteria',
            'create_payment',
            'show_payment',
        ]);
        
        $kaurKeuangan->givePermissionTo([
            'show_monitoring_report',
            'show_monitoring_report_validation',
            'show_fix_damaged_report_validation',
            'show_fix_damaged_report_validation_history',
            'show_fix_periodic_maintenance_report',
            'show_fix_final_report',
            'show_maintenance_request',
            'create_maintenance_request',
            'create_criteria',
            'create_payment',
            'show_payment',
        ]);
        
        $wakilDekan->givePermissionTo([
            'show_monitoring_report',
            'show_fix_periodic_maintenance_report',
            'show_fix_report',
            'show_maintenance_request',
            'create_maintenance_request',
            'create_payment',
            'show_payment',

        ]);

        $stafKeuangan->givePermissionTo([
            'create_payment',
            'show_payment',
            'pay_invoice',
            'mark_payment_as_paid',
        ]);
    }
}