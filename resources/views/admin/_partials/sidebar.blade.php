@php
    $authUser = auth()->user();
    $canSeeSidebar = fn ($permissions) => $authUser?->hasAnyPermission((array) $permissions) ?? false;

    $canDashboard = $canSeeSidebar(['dashboard-view-dashboard']);

    $accountingLinks = [
        ['label' => 'Companies', 'icon' => 'fa-building', 'route' => route('admin.master-data.index', 'companies'),
            'permissions' => ['companies-view-companies', 'companies-manage-companies']],
        ['label' => 'Customers', 'icon' => 'fa-users', 'route' => route('admin.master-data.index', 'customers'),
            'permissions' => ['customers-view-customers', 'customers-manage-customers']],
        ['label' => 'Services', 'icon' => 'fa-handshake', 'route' => route('admin.master-data.index', 'services'),
            'permissions' => ['services-view-services', 'services-manage-services']],
        ['label' => 'Products', 'icon' => 'fa-box', 'route' => route('admin.master-data.index', 'products'),
            'permissions' => ['products-view-products', 'products-manage-products']],
        ['label' => 'Tax Classes', 'icon' => 'fa-percent', 'route' => route('admin.master-data.index', 'tax-classes'),
            'permissions' => ['tax-classes-view-tax-classes', 'tax-classes-manage-tax-classes']],
        ['label' => 'Label Master', 'icon' => 'fa-tags', 'route' => route('admin.master-data.index', 'labels'),
            'permissions' => ['labels-view-labels', 'labels-manage-labels']],
    ];
    $visibleAccountingLinks = collect($accountingLinks)->filter(fn ($link) => $canSeeSidebar($link['permissions']));
    $canTransactions = $canSeeSidebar(['transactions-view-transactions', 'transactions-manage-transactions']);
    $canCertificates = $canSeeSidebar(['certificates-view-certificates', 'certificates-manage-certificates']);

    $isDeveloperAdmin = $authUser?->roleModel?->slug === 'developer-admin';
    $canSettings = $isDeveloperAdmin && $canSeeSidebar(['settings-view-settings', 'settings-manage-settings']);
    $canMasterUsers = $isDeveloperAdmin && $canSeeSidebar(['users-view-users', 'users-manage-users']);
    $canMasterRoles = $isDeveloperAdmin && $canSeeSidebar(['roles-view-roles', 'roles-manage-roles']);
    $canMasterPermissions = $isDeveloperAdmin && $canSeeSidebar(['permissions-view-permissions', 'permissions-manage-permissions']);
    $canMasterControl = $isDeveloperAdmin
        && ($canSeeSidebar(['master-control-view-master-control', 'master-control-manage-master-control'])
            || $canMasterUsers
            || $canMasterRoles
            || $canMasterPermissions
            || $canSettings);

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

            @if ($visibleAccountingLinks->isNotEmpty())
                <hr style="margin: 1rem 22px; color:var(--color-border-100);">
                <li class="dropdown">
                    <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-database"></i>
                        <span class="link-name">Master Data</span>
                    </button>
                    <ul class="dropdown-menu">
                        @foreach($visibleAccountingLinks as $link)
                            <li class="main-li">
                                <a class="dropdown-item nav-link navJS" href="{{ $link['route'] }}">
                                    <i class="fa-solid {{ $link['icon'] }}"></i>
                                    <span class="link-name"><i class="fa-solid fa-minus"></i> {{ $link['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </li>
            @endif

            @if ($canTransactions)
                <li class="main-li">
                    <a href="{{ route('admin.transactions.index') }}" class="nav-link navJS">
                        <i class="fa-solid fa-money-bill-transfer"></i>
                        <span class="link-name">Income & Expenses</span>
                    </a>
                </li>
            @endif

            @if ($canCertificates)
                <li class="main-li">
                    <a href="{{ route('admin.certificates.index') }}" class="nav-link navJS">
                        <i class="fa-solid fa-certificate"></i>
                        <span class="link-name">Certificates</span>
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
                        @if ($canSettings)
                            <li class="main-li">
                                <a class="dropdown-item nav-link navJS" href="{{ route('admin.settings.index') }}">
                                    <i class="fa-solid fa-gear"></i>
                                    <span class="link-name"><i class="fa-solid fa-minus"></i> Settings</span>
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
        </ul>
    </div>
</nav>
