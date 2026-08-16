@extends('layouts.admin')

@section('head')
    <title>Users</title>
    <meta name="description" content="Manage admin users.">

    @include('admin._partials.head.g-links')
    @include('admin._partials.head.g-css-files')
    @include('admin._partials.head.g-js-files')
@endsection

@section('body')
    @include('admin._partials.preloader')
    @include('admin._partials.sidebar')
    @include('admin._partials.header')

    <section class="wrapper">
        <main class="dash-content">
            @include('admin._partials.breadcrumb')

            <h4 class="hd-lg">Users</h4>

            <div>
                <h6 class="hd-sm">Total Result: <span>{{ $users->total() }}</span></h6>

                <div class="dataTable-HD">
                    <div>
                        @if(auth()->user()->hasAnyPermission(['users-create-users', 'users-manage-users']))
                            <a href="{{ route('admin.users.create') }}" type="button" class="btn-sm btn-sec">
                                <i class="fa-solid fa-plus i-mr"></i> Create New
                            </a>
                        @endif
                    </div>

                    <form method="GET" style="flex-grow: 1; max-width: 480px;">
                        <input type="search" name="search" class="form-control" placeholder="Search"
                               value="{{ request('search') }}">
                        <span class="search-base">Search By: Name, Username, Email, Mobile</span>
                    </form>
                </div>

                @if($canPermanentlyDelete && $users->whereNotNull('deleted_at')->isNotEmpty())
                    <form class="mt-3" action="{{ route('admin.users.empty-trash') }}" method="POST"
                          onsubmit="return confirm('Permanently delete every eligible user in trash?')">
                        @csrf
                        @method('DELETE')
                        <button class="btn-sm btn-sec-outline" type="submit">
                            <i class="fa-solid fa-trash-can i-mr"></i>Empty User Trash
                        </button>
                    </form>
                @endif

                @include('admin.users._partials.table', [
                    'users' => $users,
                    'canPermanentlyDelete' => $canPermanentlyDelete,
                ])
            </div>
        </main>
    </section>
@endsection
