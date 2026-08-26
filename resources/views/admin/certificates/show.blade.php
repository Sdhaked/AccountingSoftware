@extends('layouts.admin')

@section('head')
    <title>Certificate {{ $certificate->certificate_number }}</title>
    @include('admin._partials.head.g-links')
    @include('admin._partials.head.g-css-files')
    @include('admin._partials.head.g-js-files')
@endsection

@section('body')
    @include('admin._partials.preloader')
    @include('admin._partials.sidebar')
    @include('admin._partials.header')
    <section class="wrapper"><main class="dash-content">
        @include('admin._partials.breadcrumb')
        <h4 class="hd-lg">Certificate {{ $certificate->certificate_number }}</h4>
        <div class="table-responsive"><table class="table"><tbody>
            <tr><th>Certificate ID</th><td>{{ $certificate->certificate_number }}</td></tr>
            <tr><th>Date of Issue</th><td>{{ $certificate->issued_at->format('d M Y') }}</td></tr>
            <tr><th>Expiry Date</th><td>{{ $certificate->expires_at->format('d M Y') }}</td></tr>
            <tr><th>Customer Name</th><td>{{ $certificate->customer->name }}</td></tr>
            <tr><th>Company</th><td>{{ $certificate->company->name }}</td></tr>
            <tr><th>Course</th><td>{{ $certificate->course_name }}</td></tr>
            <tr><th>Instructor</th><td>{{ $certificate->instructor_name }}</td></tr>
        </tbody></table></div>
        <a class="btn-md btn-sec" href="{{ route('admin.certificates.download', $certificate) }}"><i class="fa-solid fa-download i-mr"></i> Download PDF</a>
        <a class="btn-md btn-sec-outline" href="{{ route('admin.certificates.index') }}">Back</a>
    </main></section>
@endsection
