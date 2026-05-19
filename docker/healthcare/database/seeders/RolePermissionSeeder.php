<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            'patients.view',
            'patients.create',
            'patients.update',
            'patients.delete',

            'orders.create',
            'orders.verify',

            'medications.administer',

            'billing.manage',
        ];

        foreach ($permissions as $permission) {

            Permission::firstOrCreate([
                'name' => $permission,
            ]);
        }

        $doctor = Role::firstOrCreate([
            'name' => 'doctor',
        ]);

        $doctor->givePermissionTo([
            'patients.view',
            'patients.create',
            'patients.update',
            'orders.create',
        ]);
    }
}