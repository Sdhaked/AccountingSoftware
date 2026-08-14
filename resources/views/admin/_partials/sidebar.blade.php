@php
    $permissionTablesReady = \Illuminate\Support\Facades\Schema::hasTable('permissions')
        && \Illuminate\Support\Facades\Schema::hasTable('role_permissions');
    $sidebarPermissionSlugs = collect();
    $authUser = auth()->user();

    if ($permissionTablesReady && $authUser?->role) {
        $sidebarPermissionSlugs = \App\Models\Permission::query()
            ->join('role_permissions', 'permissions.id', '=', 'role_permissions.permission_id')
            ->where('role_permissions.role_id', $authUser->role)
            ->pluck('permissions.slug');
    }

    $canSeeSidebar = function ($permissions) use ($permissionTablesReady, $sidebarPermissionSlugs) {
        if (!$permissionTablesReady) {
            return true;
        }

        return $sidebarPermissionSlugs->intersect((array) $permissions)->isNotEmpty();
    };

    $canDashboard = $canSeeSidebar(['dashboard-view-dashboard']);

    $canMasterUsers = $canSeeSidebar(['users-view-users', 'users-manage-users']);
    $canMasterRoles = $canSeeSidebar(['roles-view-roles', 'roles-manage-roles']);
    $canMasterPermissions = $canSeeSidebar(['permissions-view-permissions', 'permissions-manage-permissions']);
    $canMasterControl = $canSeeSidebar(['master-control-view-master-control', 'master-control-manage-master-control'])
        || $canMasterUsers
        || $canMasterRoles
        || $canMasterPermissions;

@endphp

<nav class="side-nav">
    <div class="menu-items">
        <ul class="nav-ul">
            @if ($canDashboard)
                <li class="main-li">
                    <a href="{{ route('admin.dashboard.index') }}" class="nav-link navJS">
                        <i class="fa-solid fa-gauge-high"></i>
                        <span class="link-name">Dashboard</span>
                    </a>
                </li>
            @endif

            @if ($canMasterControl)
                <hr style="margin: 1rem  22px; color:var(--color-border-100);">

                <li class="dropdown">
                    <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-user-shield"></i>
                        <span class="link-name">Master Control</span>
                    </button>
                    <ul class="dropdown-menu">
                        @if ($canMasterUsers)
                            <li class="main-li">
                                <a class="dropdown-item nav-link navJS" href="{{ route('admin.users.index') }}">
                                    <i class="fa-regular fa-circle-dot"></i>
                                    <span class="link-name"><i class="fa-solid fa-minus"></i> Users</span>
                                </a>
                            </li>
                        @endif
                        @if ($canMasterRoles)
                            <li class="main-li">
                                <a class="dropdown-item nav-link navJS" href="{{ route('admin.roles.index') }}">
                                    <i class="fa-regular fa-circle-dot"></i>
                                    <span class="link-name"><i class="fa-solid fa-minus"></i> Roles</span>
                                </a>
                            </li>
                        @endif
                        @if ($canMasterPermissions)
                            <li class="main-li">
                                <a class="dropdown-item nav-link navJS" href="{{ route('admin.permissions.index') }}">
                                    <i class="fa-regular fa-circle-dot"></i>
                                    <span class="link-name"><i class="fa-solid fa-minus"></i> Permissions</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
        </ul>
    </div>
</nav>
