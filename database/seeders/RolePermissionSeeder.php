<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeds the seven canonical roles and their starter permissions.
 *
 * Roles (from config/makeen.php):
 *   - super_admin           — total access, all panels
 *   - excellence_manager    — manages excellence team & reviews initiatives
 *   - excellence_member     — supporting excellence-team member
 *   - donor_admin           — funding-organization admin
 *   - consultant            — assigned advisor on initiative(s)
 *   - association_manager   — association lead user (created at registration)
 *   - association_member    — additional association staff
 *
 * Sprint 1 permissions are intentionally coarse-grained ("manage_users",
 * "view_initiatives") to unblock panel work. Sprint 2 will refine them
 * to per-resource verbs (initiative.create, initiative.submit, etc.).
 */
class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            // System-wide
            'manage_users',
            'manage_organizations',
            'manage_roles',
            'view_activity_log',
            'manage_reference_data',

            // Initiative lifecycle (placeholders — fleshed out in Sprint 3)
            'view_initiatives',
            'create_initiatives',
            'edit_initiatives',
            'submit_initiatives',
            'review_initiatives',
            'approve_initiatives',

            // Operational
            'manage_visits',
            'manage_monthly_reports',
            'manage_payments',
            'manage_kpis',
            'manage_evaluations',
            'manage_tickets',
            'manage_consultations',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission);
        }

        // Permissions cache must be flushed AFTER creation so that
        // Role::syncPermissions() can resolve the freshly inserted rows.
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $rolesToPermissions = [
            'super_admin' => $permissions,

            'excellence_manager' => [
                'manage_organizations', 'view_activity_log', 'manage_reference_data',
                'view_initiatives', 'create_initiatives', 'edit_initiatives',
                'review_initiatives', 'approve_initiatives',
                'manage_visits', 'manage_monthly_reports', 'manage_kpis',
                'manage_evaluations', 'manage_tickets',
                'manage_consultations',
            ],

            'excellence_member' => [
                'view_initiatives', 'review_initiatives',
                'manage_visits', 'manage_monthly_reports',
                'manage_kpis', 'manage_tickets',
                'manage_consultations',
            ],

            'donor_admin' => [
                'view_initiatives', 'approve_initiatives',
                'manage_payments', 'manage_monthly_reports', 'manage_evaluations',
                'manage_tickets',
            ],

            'consultant' => [
                'view_initiatives', 'review_initiatives',
                'manage_visits', 'manage_monthly_reports',
                'manage_kpis', 'manage_tickets',
            ],

            'association_manager' => [
                'view_initiatives', 'create_initiatives', 'edit_initiatives',
                'submit_initiatives', 'manage_kpis', 'manage_tickets',
                'manage_evaluations',
                'manage_consultations',
            ],

            'association_member' => [
                'view_initiatives', 'edit_initiatives', 'manage_tickets',
                'manage_consultations',
            ],
        ];

        foreach ($rolesToPermissions as $roleName => $perms) {
            $role = Role::findOrCreate($roleName);
            $role->syncPermissions($perms);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
