@extends('layouts.admin')

@section('head')
    <title>{{ $definition['singular'] }} Details</title>
    @include('admin._partials.head.g-links')
    @include('admin._partials.head.g-css-files')
    @include('admin._partials.head.g-js-files')
@endsection

@section('body')
    @php
        $currencySymbol = config('santrains.currency_symbol', '€');
    @endphp
    @include('admin._partials.preloader')
    @include('admin._partials.sidebar')
    @include('admin._partials.header')
    <section class="wrapper">
        <main class="dash-content">
            @include('admin._partials.breadcrumb')
            <h4 class="hd-lg">{{ $definition['singular'] }} Details</h4>
            <div class="table-responsive">
                <table class="table">
                    <tbody>
                    @foreach($definition['fields'] as $field)
                        @php
                            $value = isset($field['relation'])
                                ? data_get($item, $field['relation'] . '.name')
                                : data_get($item, $field['name']);
                        @endphp
                        <tr>
                            <th style="width:240px">{{ $field['label'] }}</th>
                            <td>
                                @if(($field['format'] ?? null) === 'money')
                                    {{ $currencySymbol }}{{ number_format((float) $value, 2) }}
                                @elseif(($field['format'] ?? null) === 'percentage')
                                    {{ rtrim(rtrim(number_format((float) $value, 3), '0'), '.') }}%
                                @else
                                    {!! nl2br(e(filled($value) ? $value : 'N/A')) !!}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            <a class="btn-md btn-sec" href="{{ route('admin.master-data.edit', [$entity, $item->id]) }}">Edit</a>
            <a class="btn-md btn-sec-outline" href="{{ route('admin.master-data.index', $entity) }}">Back</a>
        </main>
    </section>
@endsection
