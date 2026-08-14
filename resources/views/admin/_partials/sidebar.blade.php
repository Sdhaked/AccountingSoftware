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

    $canHomePage = $canSeeSidebar(['home-page-content-view-home-page-content', 'home-page-content-manage-home-page-content', 'page-content-view-page-content', 'page-content-manage-page-content']);
    $canAboutPage = $canSeeSidebar(['about-page-content-view-about-page-content', 'about-page-content-manage-about-page-content', 'page-content-view-page-content', 'page-content-manage-page-content']);
    $canContactPage = $canSeeSidebar(['contact-page-content-view-contact-page-content', 'contact-page-content-manage-contact-page-content', 'page-content-view-page-content', 'page-content-manage-page-content']);
    $canEventArchivePage = $canSeeSidebar(['event-archive-page-content-view-event-archive-page-content', 'event-archive-page-content-manage-event-archive-page-content', 'page-content-view-page-content', 'page-content-manage-page-content']);
    $canTicketsPage = $canSeeSidebar(['tickets-page-content-view-tickets-page-content', 'tickets-page-content-manage-tickets-page-content', 'page-content-view-page-content', 'page-content-manage-page-content']);
    $canTermsPage = $canSeeSidebar(['terms-page-content-view-terms-page-content', 'terms-page-content-manage-terms-page-content', 'page-content-view-page-content', 'page-content-manage-page-content']);
    $canPolicyPage = $canSeeSidebar(['policy-page-content-view-policy-page-content', 'policy-page-content-manage-policy-page-content', 'page-content-view-page-content', 'page-content-manage-page-content']);
    $canPageContent = $canHomePage || $canAboutPage || $canContactPage || $canEventArchivePage || $canTicketsPage || $canTermsPage || $canPolicyPage;

    $canMainHeroSlider = $canSeeSidebar(['main-hero-slider-view-main-hero-slider', 'main-hero-slider-manage-main-hero-slider']);
    $canMainInfoSlider = $canSeeSidebar(['main-info-slider-view-main-info-slider', 'main-info-slider-manage-main-info-slider']);
    $canMainGallery = $canSeeSidebar(['main-gallery-view-main-gallery', 'main-gallery-manage-main-gallery']);

    $canShowSiteContentGroup = $canMasterControl || $canPageContent || $canMainHeroSlider || $canMainInfoSlider || $canMainGallery;
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

            @if ($canShowSiteContentGroup)
                <hr style="margin: 1rem  22px; color:var(--color-border-100);">

                <li class="label-li">Site content<br/> (Global Content)</li>

                @if ($canMasterControl)
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

                @if ($canPageContent)
                    <li class="dropdown">
                        <button class="dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fa-solid fa-file-signature"></i>
                            <span class="link-name">Page Content</span>
                        </button>
                        <ul class="dropdown-menu">
                            @if ($canHomePage)
                                <li class="main-li">
                                    <a class="dropdown-item nav-link navJS" href="{{ route('admin.pages.home.index') }}">
                                        <i class="fa-regular fa-circle-dot"></i>
                                        <span class="link-name"><i class="fa-solid fa-minus"></i> Home</span>
                                    </a>
                                </li>
                            @endif
                            @if ($canAboutPage)
                                <li class="main-li">
                                    <a class="dropdown-item nav-link navJS" href="{{ route('admin.pages.about.index') }}">
                                        <i class="fa-regular fa-circle-dot"></i>
                                        <span class="link-name"><i class="fa-solid fa-minus"></i> About</span>
                                    </a>
                                </li>
                            @endif
                            @if ($canContactPage)
                                <li class="main-li">
                                    <a class="dropdown-item nav-link navJS" href="{{ route('admin.pages.contact.index') }}">
                                        <i class="fa-regular fa-circle-dot"></i>
                                        <span class="link-name"><i class="fa-solid fa-minus"></i> Contact</span>
                                    </a>
                                </li>
                            @endif
                            @if ($canEventArchivePage)
                                <li class="main-li">
                                    <a class="dropdown-item nav-link navJS" href="{{ route('admin.pages.event_archive.index') }}">
                                        <i class="fa-regular fa-circle-dot"></i>
                                        <span class="link-name"><i class="fa-solid fa-minus"></i> Event Archive</span>
                                    </a>
                                </li>
                            @endif
                            @if ($canTicketsPage)
                                <li class="main-li">
                                    <a class="dropdown-item nav-link navJS" href="{{ route('admin.pages.tickets.index') }}">
                                        <i class="fa-regular fa-circle-dot"></i>
                                        <span class="link-name"><i class="fa-solid fa-minus"></i> Tickets</span>
                                    </a>
                                </li>
                            @endif
                            @if ($canTermsPage)
                                <li class="main-li">
                                    <a class="dropdown-item nav-link navJS" href="{{ route('admin.pages.terms') }}">
                                        <i class="fa-regular fa-circle-dot"></i>
                                        <span class="link-name"><i class="fa-solid fa-minus"></i> T&amp;C</span>
                                    </a>
                                </li>
                            @endif
                            @if ($canPolicyPage)
                                <li class="main-li">
                                    <a class="dropdown-item nav-link navJS" href="{{ route('admin.pages.policy') }}">
                                        <i class="fa-regular fa-circle-dot"></i>
                                        <span class="link-name"><i class="fa-solid fa-minus"></i> Policy</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                @if ($canMainHeroSlider)
                    <li class="main-li">
                        <a href="{{ route('admin.sliders.hero.index') }}" class="nav-link navJS">
                            <i class="fa-solid fa-panorama"></i>
                            <span class="link-name">Main Hero Slider</span>
                        </a>
                    </li>
                @endif

                @if ($canMainInfoSlider)
                    <li class="main-li">
                        <a href="{{ route('admin.sliders.info.index') }}" class="nav-link navJS">
                            <i class="fa-solid fa-sliders"></i>
                            <span class="link-name">Main Info Slider</span>
                        </a>
                    </li>
                @endif

                @if ($canMainGallery)
                    <li class="main-li">
                        <a href="{{ route('admin.gallery.index') }}" class="nav-link navJS">
                            <i class="fa-regular fa-images"></i>
                            <span class="link-name">Main Gallery</span>
                        </a>
                    </li>
                @endif
            @endif
        </ul>
    </div>
</nav>
