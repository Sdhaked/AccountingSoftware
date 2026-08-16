@php
    if (!isset($breadcrumb_title)) {
        $breadcrumb_title = match (true) {
            request()->routeIs('admin.settings.*') => 'Settings',
            request()->routeIs('admin.transactions.*') => 'Income & Expenses',
            request()->routeIs('admin.certificates.*') => 'Certificates',
            request()->routeIs('admin.master-data.*') => $definition['title'] ?? 'Master Data',
            request()->routeIs('admin.users.*') => 'Users',
            request()->routeIs('admin.roles.*') => 'Roles',
            request()->routeIs('admin.permissions.*') => 'Permissions',
            request()->routeIs('profile*') => 'Profile',
            default => 'Dashboard',
        };
    }
    $breadcrumb_items = $breadcrumb_items ?? [];
@endphp

<section class="breadcrumb-sec">
    <h5 class="pTitle">{{ $breadcrumb_title }}</h5>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            @unless(request()->routeIs('admin.dashboard.index'))
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard.index') }}">Dashboard</a></li>
            @endunless
            @if(!empty($breadcrumb_items))
                @foreach($breadcrumb_items as $item)
                    @if($loop->last)
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $item['title'] }}
                        </li>
                    @else
                        <li class="breadcrumb-item">
                            @if(isset($item['url']))
                                <a href="{{ $item['url'] }}">{{ $item['title'] }}</a>
                            @else
                                {{ $item['title'] }}
                            @endif
                        </li>
                    @endif
                @endforeach
            @else
                <li class="breadcrumb-item active" aria-current="page">
                    {{ $breadcrumb_title }}
                </li>
            @endif
        </ol>
    </nav>
</section>
