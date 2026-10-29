<?php

namespace Database\Seeders\User;

use App\Helpers\PermissionHelper;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    const hs = [
        PermissionHelper::ACCESS_DASHBOARD => [PermissionHelper::TYPE_READ],
    ];

    const sales = [
        PermissionHelper::ACCESS_DASHBOARD => [PermissionHelper::TYPE_READ],
    ];

    const client = [
        PermissionHelper::ACCESS_DASHBOARD => [PermissionHelper::TYPE_READ],
    ];

    public function run(): void
    {

        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        // create role
        $role = Role::create(['name' => User::ROLE_SUPERADMIN]);
        foreach (PermissionHelper::ACCESS_TYPE_ALL as $access => $types) {
            foreach ($types as $type) {
                $role->givePermissionTo(PermissionHelper::transform($access, $type));
            }
        }
    }
}
