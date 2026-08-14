<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = array_merge(
            $this->permissionsFor('Dashboard', [
                'View Dashboard',
            ]),
            $this->permissionsFor('Admin Auth', [
                'Login Admin',
                'Logout Admin',
                'Reset Admin Password',
            ]),
            $this->permissionsFor('Profile', [
                'Manage Profile',
                'View Profile',
                'Edit Profile',
                'Change Profile Password',
            ]),
            $this->permissionsFor('Settings', [
                'Manage Settings',
                'View Settings',
            ]),
            $this->permissionsFor('Master Control', [
                'Manage Master Control',
                'View Master Control',
            ]),
            $this->permissionsFor('Users', [
                'Manage Users',
                'View Users',
                'Create Users',
                'Edit Users',
                'Delete Users',
            ]),
            $this->permissionsFor('Roles', [
                'Manage Roles',
                'View Roles',
                'Create Roles',
                'Edit Roles',
                'Delete Roles',
            ]),
            $this->permissionsFor('Permissions', [
                'Manage Permissions',
                'View Permissions',
                'Create Permissions',
                'Edit Permissions',
                'Delete Permissions',
            ])
        );

        $developerOnlySlugs = $this->slugsFor([
            'Master Control' => [
                'Manage Master Control',
                'View Master Control',
            ],
            'Users' => [
                'Manage Users',
                'View Users',
                'Create Users',
                'Edit Users',
                'Delete Users',
            ],
            'Roles' => [
                'Manage Roles',
                'View Roles',
                'Create Roles',
                'Edit Roles',
                'Delete Roles',
            ],
            'Permissions' => [
                'Manage Permissions',
                'View Permissions',
                'Create Permissions',
                'Edit Permissions',
                'Delete Permissions',
            ],
        ]);

        $roles = [
            ['name' => 'admin', 'description' => 'Admin role with standard seeded permissions.'],
            ['name' => 'super admin', 'description' => 'Super admin role with standard seeded permissions.'],
            ['name' => 'developer admin', 'description' => 'Developer admin role with all seeded permissions.'],
        ];

        DB::transaction(function () use ($permissions, $developerOnlySlugs, $roles) {
            $permissionSlugs = [];

            foreach ($permissions as $permission) {
                $slug = Str::slug($permission['module'] . ' ' . $permission['name']);
                $permissionSlugs[] = $slug;

                Permission::updateOrCreate(
                    ['slug' => $slug],
                    [
                        'module' => $permission['module'],
                        'name' => $permission['name'],
                        'description' => null,
                    ]
                );
            }

            $developerPermissionIds = Permission::whereIn('slug', $permissionSlugs)
                ->pluck('id')
                ->all();

            $standardPermissionIds = Permission::whereIn('slug', $permissionSlugs)
                ->whereNotIn('slug', $developerOnlySlugs)
                ->pluck('id')
                ->all();

            foreach ($roles as $role) {
                $roleModel = Role::updateOrCreate(
                    ['slug' => Str::slug($role['name'])],
                    [
                        'name' => $role['name'],
                        'description' => $role['description'],
                    ]
                );

                $roleModel->permissions()->sync(
                    $roleModel->slug === 'developer-admin'
                        ? $developerPermissionIds
                        : $standardPermissionIds
                );
            }
        });
    }

    private function permissionsFor(string $module, array $names): array
    {
        return array_map(function (string $name) use ($module) {
            return [
                'module' => $module,
                'name' => $name,
            ];
        }, $names);
    }

    private function slugsFor(array $permissionsByModule): array
    {
        $slugs = [];

        foreach ($permissionsByModule as $module => $names) {
            foreach ($names as $name) {
                $slugs[] = Str::slug($module . ' ' . $name);
            }
        }

        return $slugs;
    }
}
