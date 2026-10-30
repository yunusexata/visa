<?php

use App\Helpers\PermissionHelper;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $role = Role::firstOrCreate(['name' => User::ROLE_SUPERADMIN]);

        $types = [
            PermissionHelper::TYPE_CREATE,
            PermissionHelper::TYPE_READ,
            PermissionHelper::TYPE_UPDATE,
            PermissionHelper::TYPE_DELETE,
        ];

        foreach ($types as $type) {
            $name = PermissionHelper::transform(PermissionHelper::ACCESS_VISA_FORM, $type);
            $permission = Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
            $role->givePermissionTo($permission);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $types = [
            PermissionHelper::TYPE_CREATE,
            PermissionHelper::TYPE_READ,
            PermissionHelper::TYPE_UPDATE,
            PermissionHelper::TYPE_DELETE,
        ];

        foreach ($types as $type) {
            $name = PermissionHelper::transform(PermissionHelper::ACCESS_VISA_FORM, $type);
            Permission::where('name', $name)->delete();
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
};
