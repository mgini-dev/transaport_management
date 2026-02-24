<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
  
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $permissions = [
            'customers.view',
            'customers.create',
            'trips.view',
            'trips.view_all',
            'trips.create',
            'trips.close',
            'orders.view',
            'orders.view_all',
            'orders.create',
            'orders.process',
            'orders.status.update',
            'orders.view_distance',
            'fleet.view',
            'fleet.view_all',
            'fleet.create',
            'fleet.assign',
            'drivers.view',
            'drivers.view_all',
            'drivers.create',
            'drivers.update',
            'fuel.view',
            'fuel.view_all',
            'fuel.create',
            'fuel.approve.supervisor',
            'fuel.approve.accounting',
            'admin.users.manage',
            'admin.logs.view',
            'admin.dashboard.view_all',
        ];

        foreach ($permissions as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $roles = [
            'Order Creator' => ['customers.view', 'customers.create', 'trips.view', 'orders.view', 'orders.create'],
            'Fleet Officer' => ['trips.view', 'orders.view', 'orders.view_all', 'orders.process', 'orders.status.update', 'orders.view_distance', 'fleet.view', 'fleet.view_all', 'fleet.create', 'fleet.assign', 'drivers.view', 'drivers.view_all', 'drivers.create', 'drivers.update'],
            'Fuel Officer' => ['orders.view', 'orders.view_all', 'orders.view_distance', 'fuel.view', 'fuel.view_all', 'fuel.create'],
            'Approver' => ['trips.view', 'trips.view_all', 'orders.view', 'orders.view_all', 'fuel.view', 'fuel.view_all', 'fuel.approve.supervisor'],
            'Accountant' => ['trips.view', 'trips.view_all', 'orders.view', 'orders.view_all', 'fuel.view', 'fuel.view_all', 'fuel.approve.accounting'],
            'Chief Admin' => $permissions,
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::query()->firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($rolePermissions);
        }

        $admin = User::query()->firstOrCreate(
            ['email' => env('NMIS_ADMIN_EMAIL', 'admin@nmis.local')],
            [
                'name' => env('NMIS_ADMIN_NAME', 'NMIS Admin'),
                'password' => Hash::make(env('NMIS_ADMIN_PASSWORD', 'Password@123')),
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $admin->assignRole('Chief Admin');
    }
}
