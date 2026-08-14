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
            $this->permissionsFor('Page Content', [
                'Manage Page Content',
                'View Page Content',
                'Edit Page Content',
            ]),
            $this->permissionsFor('Home Page Content', [
                'Manage Home Page Content',
                'View Home Page Content',
                'Edit Home Page Content',
            ]),
            $this->permissionsFor('About Page Content', [
                'Manage About Page Content',
                'View About Page Content',
                'Edit About Page Content',
            ]),
            $this->permissionsFor('Contact Page Content', [
                'Manage Contact Page Content',
                'View Contact Page Content',
                'Edit Contact Page Content',
            ]),
            $this->permissionsFor('Event Archive Page Content', [
                'Manage Event Archive Page Content',
                'View Event Archive Page Content',
                'Edit Event Archive Page Content',
            ]),
            $this->permissionsFor('Tickets Page Content', [
                'Manage Tickets Page Content',
                'View Tickets Page Content',
                'Edit Tickets Page Content',
            ]),
            $this->permissionsFor('Terms Page Content', [
                'Manage Terms Page Content',
                'View Terms Page Content',
                'Edit Terms Page Content',
            ]),
            $this->permissionsFor('Policy Page Content', [
                'Manage Policy Page Content',
                'View Policy Page Content',
                'Edit Policy Page Content',
            ]),
            $this->permissionsFor('Main Hero Slider', [
                'Manage Main Hero Slider',
                'View Main Hero Slider',
                'Create Main Hero Slider',
                'Edit Main Hero Slider',
                'Delete Main Hero Slider',
            ]),
            $this->permissionsFor('Main Info Slider', [
                'Manage Main Info Slider',
                'View Main Info Slider',
                'Create Main Info Slider',
                'Edit Main Info Slider',
                'Delete Main Info Slider',
            ]),
            $this->permissionsFor('Main Gallery', [
                'Manage Main Gallery',
                'View Main Gallery',
                'Create Main Gallery',
                'Edit Main Gallery',
                'Delete Main Gallery',
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
