@extends('dashboards.users.layouts.user-dash-layout')

@section('title', 'Error Logs')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .logs-container {
        padding: 20px;
        background: #f8f9fe;
    }
    .content-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
    .content-card .card-header {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        background: linear-gradient(87deg, #fb6340, #fbb140) !important;
        border-radius: 15px 15px 0 0;
        color: white;
    }
    .content-card .card-title {
        margin: 0;
        color: white;
        font-size: 1.25rem;
        font-weight: 600;
    }
    .content-card .card-subtitle {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.875rem;
        text-transform: uppercase;
    }
    .table {
        margin: 0;
    }
    .table th {
        font-size: 0.875rem;
        font-weight: 600;
        padding: 12px 20px;
        color: #525f7f;
        border-bottom: 1px solid #e9ecef;
        background: #f6f9fc;
    }
    .table td {
        padding: 12px 20px;
        vertical-align: middle;
        border-top: 1px solid #e9ecef;
        color: #525f7f;
        font-size: 0.875rem;
    }
    .table tr:hover {
        background-color: #f8f9fa;
    }
    .badge {
        padding: 5px 10px;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 5px;
    }
    .filter-form {
        padding: 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        border-left: 4px solid #fb6340;
    }
    .pagination {
        justify-content: center;
        margin-top: 20px;
    }
    pre {
        white-space: pre-wrap;
        word-wrap: break-word;
        font-size: 12px;
        line-height: 1.4;
    }
    .btn-primary {
        background-color: #fb6340;
        border-color: #fb6340;
    }
    .btn-primary:hover {
        background-color: #fa3a0e;
        border-color: #fa3a0e;
    }
    .btn-secondary {
        background-color: #8898aa;
        border-color: #8898aa;
    }
    .btn-secondary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
    }
    .btn-info {
        background-color: #11cdef;
        border-color: #11cdef;
    }
    .btn-info:hover {
        background-color: #0da5c0;
        border-color: #0da5c0;
    }
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
    .form-control:focus, .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #fb6340 !important;
        box-shadow: 0 0 0 0.2rem rgba(251, 99, 64, 0.25) !important;
    }
    .page-header {
        background: linear-gradient(87deg, #fb6340, #fbb140);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .page-title-container {
        flex: 1;
    }
    .page-title {
        margin: 0;
        font-weight: 600;
    }
    .page-subtitle {
        opacity: 0.8;
        margin-bottom: 0;
    }
    .modal-header {
        background: linear-gradient(87deg, #fb6340, #fbb140);
        color: white;
        border-radius: 0.3rem 0.3rem 0 0;
    }
    .modal-title {
        color: white;
    }
    .close {
        color: white;
        text-shadow: none;
        opacity: 0.8;
    }
    .close:hover {
        color: white;
        opacity: 1;
    }
    .bg-danger {
        background-color: #f5365c !important;
    }
    .bg-warning {
        background-color: #ffd600 !important;
    }
    .bg-info {
        background-color: #11cdef !important;
    }
    .text-white {
        color: white !important;
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-row {
        margin-bottom: 1rem;
    }
    .filter-buttons {
        margin-top: 1.5rem;
    }
    .select2-container {
        width: 100% !important;
    }
    .back-button {
        margin-left: 15px;
    }
</style>
@endpush

@section('content')
<div class="logs-container">
    <div class="page-header">
        <div class="page-title-container">
            <h1 class="page-title">Error Logs</h1>
            <p class="page-subtitle">Monitor system errors and warnings</p>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-light back-button">
                <i class="mdi mdi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="filter-form">
        <form action="{{ route('admin.errors') }}" method="GET">
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="level">Error Level</label>
                    <select name="level" id="level" class="form-control select2-basic">
                        <option value="">All Levels</option>
                        @foreach($errorLevels as $level)
                        <option value="{{ $level }}" {{ request('level') == $level ? 'selected' : '' }}>
                            {{ ucfirst($level) }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label for="message">Message</label>
                    <input type="text" name="message" id="message" class="form-control" value="{{ request('message') }}" placeholder="Filter by message">
                </div>
                <div class="col-md-4 form-group">
                    <label for="ip_address">IP Address</label>
                    <input type="text" name="ip_address" id="ip_address" class="form-control" value="{{ request('ip_address') }}" placeholder="Filter by IP address">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="file">File</label>
                    <input type="text" name="file" id="file" class="form-control" value="{{ request('file') }}" placeholder="Filter by file path">
                </div>
                <div class="col-md-4 form-group">
                    <label for="date_from">Date From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="date_to">Date To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
            </div>
            <div class="row">
                <div class="col-12 text-right">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.errors') }}" class="btn btn-secondary ml-2">
                        <i class="mdi mdi-refresh"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Error Logs Table -->
    <div class="content-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h6 class="card-subtitle">System</h6>
                <h3 class="card-title">Error Logs</h3>
            </div>
            <div>
                <span class="badge bg-white text-primary">Total: {{ $errors->total() }}</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Level</th>
                        <th>Message</th>
                        <th>File</th>
                        <th>IP Address</th>
                        <th>User</th>
                        <th>Time</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($errors as $error)
                    <tr>
                        <td>{{ $error->id }}</td>
                        <td>
                            <span class="badge bg-{{ $error->level == 'error' ? 'danger' : ($error->level == 'warning' ? 'warning' : 'info') }} text-white">
                                {{ $error->level }}
                            </span>
                        </td>
                        <td>{{ Str::limit($error->message, 40) }}</td>
                        <td>{{ Str::limit($error->file, 20) }}</td>
                        <td>{{ $error->ip_address ?? 'Unknown' }}</td>
                        <td>
                            @if(isset($error->user_name) && $error->user_name)
                                {{ $error->user_name }}
                            @elseif($error->username)
                                {{ $error->username }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ \Carbon\Carbon::parse($error->created_at)->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#errorModal{{ $error->id }}">
                                <i class="mdi mdi-information-outline"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $errors->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- Error Details Modals -->
@foreach($errors as $error)
<div class="modal fade" id="errorModal{{ $error->id }}" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel{{ $error->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="errorModalLabel{{ $error->id }}">
                    <span class="badge bg-{{ $error->level == 'error' ? 'danger' : ($error->level == 'warning' ? 'warning' : 'info') }} text-white">
                        {{ $error->level }}
                    </span>
                    Error Details #{{ $error->id }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Level:</strong> 
                            <span class="badge bg-{{ $error->level == 'error' ? 'danger' : ($error->level == 'warning' ? 'warning' : 'info') }} text-white">
                                {{ $error->level }}
                            </span>
                        </p>
                        <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($error->created_at)->format('Y-m-d H:i:s') }}</p>
                        @if($error->file)
                        <p><strong>File:</strong> {{ $error->file }}</p>
                        @endif
                        @if($error->line)
                        <p><strong>Line:</strong> {{ $error->line }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <p><strong>IP Address:</strong> {{ $error->ip_address ?? 'Unknown' }}</p>
                        @if(isset($error->user_name) && $error->user_name)
                        <p><strong>User:</strong> {{ $error->user_name }}</p>
                        @elseif($error->username)
                        <p><strong>Username:</strong> {{ $error->username }}</p>
                        @endif
                        @if($error->url)
                        <p><strong>URL:</strong> {{ $error->url }}</p>
                        @endif
                        @if($error->method)
                        <p><strong>Method:</strong> {{ $error->method }}</p>
                        @endif
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <p><strong>Message:</strong></p>
                        <div class="p-3 bg-light rounded">
                            {{ $error->message }}
                        </div>
                    </div>
                </div>
                @if($error->user_agent)
                <div class="row mt-3">
                    <div class="col-12">
                        <p><strong>User Agent:</strong></p>
                        <div class="p-3 bg-light rounded">
                            {{ $error->user_agent }}
                        </div>
                    </div>
                </div>
                @endif
                @if($error->context)
                <div class="row mt-3">
                    <div class="col-12">
                        <p><strong>Context:</strong></p>
                        <div class="p-3 bg-light rounded" style="max-height: 150px; overflow-y: auto;">
                            <pre>{{ json_encode(json_decode($error->context), JSON_PRETTY_PRINT) }}</pre>
                        </div>
                    </div>
                </div>
                @endif
                @if($error->stack_trace)
                <div class="row mt-3">
                    <div class="col-12">
                        <p><strong>Stack Trace:</strong></p>
                        <div class="p-3 bg-light rounded" style="max-height: 200px; overflow-y: auto;">
                            <pre>{{ $error->stack_trace }}</pre>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2-basic').select2({
            width: '100%',
            dropdownParent: $('.filter-form')
        });
    });
</script>
@endpush