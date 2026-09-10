<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'create-roles',
            'edit-roles',
            'delete-roles',
            'view-roles',
            'assign-roles',
            //-----------------
            'create-clients',
            'edit-clients',
            'editpass-clients',
            'delete-clients',
            'view-admins',
            'view-clients',
            //------------------
            'view-categories',
            'create-category',
            'edit-categories',
            'delete-categories',
            //-------------------
            'view-salons',
            'create-salons',
            'edit-salon',
            'delete-salon',
            //-------------------
            'view-attributes',
            'create-attributes',
            'update-attributes',
            'delete-attributes',
            //------------------
            'view-sessions',
            'create-sessions',
            'edit-sessions',
            'delete-sessions',
            //-------------------
            'view-attributes',
            'create-attributes',
            'edit-attributes',
            'delete-attributes'



        ];

        foreach ($permissions as $permissionName) {
            Permission::updateOrCreate(
                ['name' => $permissionName],
                ['name' => $permissionName]
            );
        }
    }
}
