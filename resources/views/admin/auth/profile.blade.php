@extends('layouts.admin')

@section('head')
    <title>Profile</title>
    <meta name="description" content="lorem hdihf ffhefef e9fje9fje9fef jefje9 fefef.">

    <!----======== Head Files ======== -->
    @include('admin._partials.head.g-links')

    <!----======== CSS ======== -->
    @include('admin._partials.head.g-css-files')

    <!----======== JS ======== -->
    @include('admin._partials.head.g-js-files')
@endsection

@section('body')
    <!-- PRELOADER -->
    @include('admin._partials.preloader')

    <!-- SideBar (Nav Items) -->
    @include('admin._partials.sidebar')

    <!-- TOP HEADER -->
    @include('admin._partials.header')

    <!-- MAIN CONTENT 🥗 -->
    <section class="wrapper">
        <main class="dash-content">
            <!-- Breadcrumb -->
            @include('admin._partials.breadcrumb')

            <h4 class="hd-lg">My Profile <a href="{{ route('profile.edit') }}" class="text-prim"><i
                        class="fa-regular fa-pen-to-square"></i></a>
            </h4>

            <div class="table-responsive mt-4">
                <table class="table view-table">
                    <tbody>
                        <tr>
                            <th>Profile Pic
                            </th>
                            <td><img src="{{ $user->profile_picture ? asset('storage/' . $user->profile_picture) : asset('images/profile.jpg') }}"
                                    class="thumb-img x2"></td>
                        </tr>
                        <tr>
                            <th>Id</th>
                            <td>#{{ $user->id }}</td>
                        </tr>
                        <tr>
                            <th>Role</th>
                            <td class="text-capitalize">
                                @php
                                    $roleName = \App\Models\Role::whereKey($user->role)->value('name')
                                        ?? data_get(config('entities.user_types', []), $user->role, 'unknown');
                                @endphp
                                {{ $roleName }}
                            </td>
                        </tr>
                        <tr>
                            <th>Name
                            </th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email Id
                            </th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Mobile No
                            </th>
                            <td>{{ trim(($user->mobile_number_prefix ?? '') . ' ' . ($user->mobile_number ?? '')) ?: 'N/A' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Back Btn Sec -->
            <div class="d-flex justify-content-end my-5">
                <button class=" btn-sm btn-sec" onclick="window.history.back()">Back <i
                        class="fa-solid fa-right-to-bracket i-ml"></i></button>
            </div>
        </main>
    </section>

@endsection
