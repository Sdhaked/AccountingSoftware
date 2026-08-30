@extends('layouts.admin')

@section('head')
    <title>Certificates</title>
    @include('admin._partials.head.g-links')
    @include('admin._partials.head.g-css-files')
    @include('admin._partials.head.g-js-files')
@endsection

@section('body')
    @php
        $canCreate = auth()->user()->hasAnyPermission(['certificates-create-certificates', 'certificates-manage-certificates']);
        $canDownload = auth()->user()->hasAnyPermission(['certificates-download-certificates', 'certificates-manage-certificates']);
        $canDelete = auth()->user()->hasAnyPermission(['certificates-delete-certificates', 'certificates-manage-certificates']);
    @endphp
    @include('admin._partials.preloader')
    @include('admin._partials.sidebar')
    @include('admin._partials.header')
    <section class="wrapper">
        <main class="dash-content">
            @include('admin._partials.breadcrumb')
            <h4 class="hd-lg">Certificate List</h4>

            <div class="dataTable-HD">
                @if($canCreate)
                    <a class="btn-sm btn-sec align-self-start" href="{{ route('admin.certificates.create') }}">
                        <i class="fa-solid fa-plus i-mr"></i> Create Certificate
                    </a>
                @endif
                <form method="GET" style="flex-grow:1;max-width:480px">
                    <input class="form-control" type="search" name="search" placeholder="Certificate ID or customer"
                           value="{{ request('search') }}">
                    <label class="form-check mt-2">
                        <input class="form-check-input" type="checkbox" name="expired" value="1"
                               @checked(request()->boolean('expired')) onchange="this.form.submit()">
                        <span class="form-check-label">Expired only</span>
                    </label>
                </form>
            </div>

            @if($canDelete)
                <div class="create-event-form-box my-4">
                    <h6 class="hd-sm">Delete All Expired Certificates</h6>
                    <form method="POST" action="{{ route('admin.certificates.destroy-expired') }}"
                    onsubmit="return confirm('This permanently deletes every expired certificate. Continue?')">
                    @csrf @method('DELETE')
                    <label>Type: yes delete all exp certificates</label>
                    <div class="d-flex gap-2 flex-wrap">
                            <input class="form-control" style="max-width:420px" name="confirmation"
                                placeholder="Type: yes delete all exp certificates" required>
                            <button class="btn-sm btn-sec" type="submit">Delete Expired</button>
                        </div>
                        @error('confirmation')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </form>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table mob-view">
                    <thead><tr><th>Certificate ID</th><th>Date of Issue</th><th>Expiry Date</th><th>Customer</th><th>Company</th><th>Actions</th></tr></thead>
                    <tbody>
                    @forelse($certificates as $certificate)
                        <tr>
                            <td><div class="data-label">Certificate ID</div>{{ $certificate->certificate_number }}</td>
                            <td><div class="data-label">Date of Issue</div>{{ $certificate->issued_at->format('d M Y') }}</td>
                            <td><div class="data-label">Expiry Date</div><span class="{{ $certificate->expires_at->isPast() ? 'red' : '' }}">{{ $certificate->expires_at->format('d M Y') }}</span></td>
                            <td><div class="data-label">Customer</div>{{ $certificate->customer->name }}</td>
                            <td><div class="data-label">Company</div>{{ $certificate->company->name }}</td>
                            <td>
                                <div class="data-label">Actions</div>
                                <div class="action-row">
                                    <a class="action-btn" href="{{ route('admin.certificates.show', $certificate) }}" title="View"><i class="fa-regular fa-eye"></i></a>
                                    @if($canDownload)<a class="action-btn edit" href="{{ route('admin.certificates.download', $certificate) }}" title="Download"><i class="fa-solid fa-download"></i></a>@endif
                                    @if($canDelete)
                                        <form method="POST" action="{{ route('admin.certificates.destroy', $certificate) }}" onsubmit="return confirm('Delete this certificate?')">
                                            @csrf @method('DELETE')
                                            <button class="action-btn delete" type="submit" title="Delete"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center">No certificates found.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($certificates->hasPages())<div class="pagination"><ul>{{ $certificates->withQueryString()->links() }}</ul></div>@endif
        </main>
    </section>
@endsection
