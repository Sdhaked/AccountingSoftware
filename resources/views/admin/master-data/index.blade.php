@extends('layouts.admin')

@section('head')
    <title>{{ $definition['title'] }}</title>
    <meta name="description" content="Manage {{ strtolower($definition['title']) }}.">
    @include('admin._partials.head.g-links')
    @include('admin._partials.head.g-css-files')
    @include('admin._partials.head.g-js-files')
@endsection

@section('body')
    @php
        $currencySymbol = config('santrains.currency_symbol', '€');
        $canCreate = auth()->user()->hasAnyPermission(["{$entity}-create-{$entity}", "{$entity}-manage-{$entity}"]);
        $canEdit = auth()->user()->hasAnyPermission(["{$entity}-edit-{$entity}", "{$entity}-manage-{$entity}"]);
        $canDelete = auth()->user()->hasAnyPermission(["{$entity}-delete-{$entity}", "{$entity}-manage-{$entity}"]);
    @endphp
    @include('admin._partials.preloader')
    @include('admin._partials.sidebar')
    @include('admin._partials.header')

    <section class="wrapper">
        <main class="dash-content">
            @include('admin._partials.breadcrumb')
            <h4 class="hd-lg">{{ $definition['title'] }}</h4>

            <h6 class="hd-sm">Total Result: <span>{{ $records->total() }}</span></h6>
            <div class="dataTable-HD">
                @if($canCreate)
                    <a href="{{ route('admin.master-data.create', $entity) }}" class="btn-sm btn-sec">
                        <i class="fa-solid fa-plus i-mr"></i> Create New
                    </a>
                @endif
                <form method="GET" style="flex-grow:1;max-width:480px">
                    <input type="search" name="search" class="form-control" placeholder="Search"
                           value="{{ request('search') }}">
                </form>
            </div>

            <div class="table-responsive mt-4">
                <table class="table mob-view">
                    <thead>
                    <tr>
                        <th>S No.</th>
                        @foreach($definition['fields'] as $field)
                            <th>{{ $field['label'] }}</th>
                        @endforeach
                        <th>Actions</th>
                    </tr>
                    </thead>
                    <tbody>
                    @forelse($records as $index => $record)
                        <tr>
                            <td><div class="data-label">S No.</div>{{ $records->firstItem() + $index }}</td>
                            @foreach($definition['fields'] as $field)
                                @php
                                    $value = isset($field['relation'])
                                        ? data_get($record, $field['relation'] . '.name')
                                        : data_get($record, $field['name']);
                                @endphp
                                <td>
                                    <div class="data-label">{{ $field['label'] }}</div>
                                    @if(($field['format'] ?? null) === 'money')
                                        {{ $currencySymbol }}{{ number_format((float) $value, 2) }}
                                    @elseif(($field['format'] ?? null) === 'image')
                                        @if(filled($value))
                                            <img src="{{ asset('storage/'.$value) }}" class="thumb-img x2" alt="{{ $field['label'] }}">
                                        @else
                                            N/A
                                        @endif
                                    @elseif(($field['format'] ?? null) === 'percentage')
                                        {{ rtrim(rtrim(number_format((float) $value, 3), '0'), '.') }}%
                                    @else
                                        <div class="text-break">{{ filled($value) ? $value : 'N/A' }}</div>
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                <div class="data-label">Actions</div>
                                <div class="action-row">
                                    <a href="{{ route('admin.master-data.show', [$entity, $record->id]) }}"
                                       class="action-btn" title="View"><i class="fa-regular fa-eye"></i></a>
                                    @if($canEdit)
                                        <a href="{{ route('admin.master-data.edit', [$entity, $record->id]) }}"
                                           class="action-btn edit" title="Edit"><i class="fa-regular fa-pen-to-square"></i></a>
                                    @endif
                                    @if($canDelete)
                                        <form action="{{ route('admin.master-data.destroy', [$entity, $record->id]) }}" method="POST"
                                              onsubmit="return confirm('Are you sure you want to delete this record?')">
                                            @csrf @method('DELETE')
                                            <button class="action-btn delete" type="submit" title="Delete">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="{{ count($definition['fields']) + 2 }}" class="text-center">No records found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($records->hasPages())
                <div class="pagination"><ul>{{ $records->withQueryString()->links() }}</ul></div>
            @endif
        </main>
    </section>
@endsection
