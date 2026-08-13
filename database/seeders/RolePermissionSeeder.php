<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'companies.view',
            'companies.create',
            'companies.update',
            'companies.delete',
            'users.view',
            'users.create',
            'users.update',
            'users.delete',
            'roles.view',
            'roles.manage',
            'teams.view',
            'teams.create',
            'teams.update',
            'teams.delete',
            'settings.manage',
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $superAdmin = Role::findOrCreate('super_admin', 'web');
        $platformAdmin = Role::findOrCreate('platform_admin', 'web');
        $companyAdmin = Role::findOrCreate('company_admin', 'web');
        $manager = Role::findOrCreate('manager', 'web');
        $teamLead = Role::findOrCreate('team_lead', 'web');
        $hr = Role::findOrCreate('hr', 'web');
        $employee = Role::findOrCreate('employee', 'web');

        $superAdmin->syncPermissions($permissions);
        $platformAdmin->syncPermissions($permissions);
        $companyAdmin->syncPermissions([
            'companies.view',
            'companies.update',
            'users.view',
            'users.create',
            'users.update',
            'roles.view',
            'teams.view',
            'teams.create',
            'teams.update',
            'teams.delete',
            'settings.manage',
            'audit.view',
        ]);
        $manager->syncPermissions(['users.view', 'teams.view', 'teams.update']);
        $teamLead->syncPermissions(['users.view', 'teams.view']);
        $hr->syncPermissions(['users.view']);
        $employee->syncPermissions([]);
    }
}
