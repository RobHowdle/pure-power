<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [

            // Users
            'users.manage',

            // Blog
            'blog.create',
            'blog.edit',
            'blog.delete',
            'blog.publish',

            // Artists
            'artists.create',
            'artists.edit',
            'artists.delete',

            // Festivals
            'festivals.create',
            'festivals.edit',
            'festivals.delete',

            // Settings
            'settings.manage',
        ];


        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
            ]);
        }


        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */

        $superAdmin = Role::firstOrCreate([
            'name' => 'super-admin',
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'admin',
        ]);

        $contentManager = Role::firstOrCreate([
            'name' => 'content-manager',
        ]);

        $artistManager = Role::firstOrCreate([
            'name' => 'artist-manager',
        ]);


        /*
        |--------------------------------------------------------------------------
        | Assign permissions
        |--------------------------------------------------------------------------
        */

        // Full access
        $superAdmin->syncPermissions(
            Permission::all()
        );


        // Admin
        $admin->syncPermissions([
            'users.manage',
            'settings.manage',
        ]);


        // Content
        $contentManager->syncPermissions([
            'blog.create',
            'blog.edit',
            'blog.delete',
            'blog.publish',
            'artists.create',
            'artists.edit',
        ]);


        // Artists
        $artistManager->syncPermissions([
            'artists.create',
            'artists.edit',
            'artists.delete',
        ]);
    }
}