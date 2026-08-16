@extends('layouts.admin')

@section('head')
    <title>Dashboard</title>
    <meta name="description" content="Admin dashboard.">

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
            <h4 class="hd-lg">Hi, {{ auth()->user()->name }}</h4>
            <div class="grid-auto gap-card">
                @foreach($cards as $card)
                    <div class="create-event-form-box" style="min-height:125px;display:flex;align-items:center;gap:18px">
                        <i class="fa-solid {{ $card['icon'] }} text-prim" style="font-size:32px;min-width:40px"></i>
                        <div>
                            <div class="text-200">{{ $card['label'] }}</div>
                            <h3 style="margin:4px 0 0">{{ $card['value'] }}</h3>
                        </div>
                    </div>
                @endforeach
            </div>
        </main>
    </section>
@endsection
