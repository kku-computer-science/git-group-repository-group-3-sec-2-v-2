@extends('dashboards.users.layouts.user-dash-layout')

@section('title', 'Activity Logs')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .logs-container {
        padding: 20px;
        background: #f8f9fe;
    }
<<<<<<< HEAD
    .content-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
    }
=======

    .content-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .content-card .card-header {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
        background: linear-gradient(87deg, #11cdef, #1171ef) !important;
        border-radius: 15px 15px 0 0;
        color: white;
    }
<<<<<<< HEAD
=======

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .content-card .card-title {
        margin: 0;
        color: white;
        font-size: 1.25rem;
        font-weight: 600;
    }
<<<<<<< HEAD
=======

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .content-card .card-subtitle {
        color: rgba(255, 255, 255, 0.8);
        font-size: 0.875rem;
        text-transform: uppercase;
    }
<<<<<<< HEAD
    .table {
        margin: 0;
    }
=======

    .table {
        margin: 0;
    }

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .table th {
        font-size: 0.875rem;
        font-weight: 600;
        padding: 12px 20px;
        color: #525f7f;
        border-bottom: 1px solid #e9ecef;
        background: #f6f9fc;
    }
<<<<<<< HEAD
=======

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .table td {
        padding: 12px 20px;
        vertical-align: middle;
        border-top: 1px solid #e9ecef;
        color: #525f7f;
        font-size: 0.875rem;
    }
<<<<<<< HEAD
    .table tr:hover {
        background-color: #f8f9fa;
    }
=======

    .table tr:hover {
        background-color: #f8f9fa;
    }

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .badge {
        padding: 5px 10px;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 5px;
    }
<<<<<<< HEAD
=======

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .filter-form {
        padding: 20px;
        background: white;
        border-radius: 15px;
<<<<<<< HEAD
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
        margin-bottom: 30px;
        border-left: 4px solid #5e72e4;
    }
=======
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        border-left: 4px solid #5e72e4;
    }

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .pagination {
        justify-content: center;
        margin-top: 20px;
    }
<<<<<<< HEAD
=======

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .btn-primary {
        background-color: #5e72e4;
        border-color: #5e72e4;
    }
<<<<<<< HEAD
=======

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .btn-primary:hover {
        background-color: #324cdd;
        border-color: #324cdd;
    }
<<<<<<< HEAD
=======

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .btn-secondary {
        background-color: #8898aa;
        border-color: #8898aa;
    }
<<<<<<< HEAD
=======

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .btn-secondary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
    }
<<<<<<< HEAD
=======

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .btn-info {
        background-color: #11cdef;
        border-color: #11cdef;
    }
<<<<<<< HEAD
=======

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .btn-info:hover {
        background-color: #0da5c0;
        border-color: #0da5c0;
    }
<<<<<<< HEAD
=======

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
    }
<<<<<<< HEAD
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }
    .form-control:focus, .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #5e72e4 !important;
        box-shadow: 0 0 0 0.2rem rgba(94, 114, 228, 0.25) !important;
    }
=======

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }

    .form-control:focus,
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #5e72e4 !important;
        box-shadow: 0 0 0 0.2rem rgba(94, 114, 228, 0.25) !important;
    }

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .page-header {
        background: linear-gradient(87deg, #5e72e4, #825ee4);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
<<<<<<< HEAD
    .page-title-container {
        flex: 1;
    }
=======

    .page-title-container {
        flex: 1;
    }

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .page-title {
        margin: 0;
        font-weight: 600;
    }
<<<<<<< HEAD
=======

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .page-subtitle {
        opacity: 0.8;
        margin-bottom: 0;
    }
<<<<<<< HEAD
=======

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .modal-header {
        background: linear-gradient(87deg, #5e72e4, #825ee4);
        color: white;
        border-radius: 0.3rem 0.3rem 0 0;
    }
<<<<<<< HEAD
    .modal-title {
        color: white;
    }
=======

    .modal-title {
        color: white;
    }

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .close {
        color: white;
        text-shadow: none;
        opacity: 0.8;
    }
<<<<<<< HEAD
=======

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .close:hover {
        color: white;
        opacity: 1;
    }
<<<<<<< HEAD
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-row {
        margin-bottom: 1rem;
    }
    .filter-buttons {
        margin-top: 1.5rem;
    }
=======

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-row {
        margin-bottom: 1rem;
    }

    .filter-buttons {
        margin-top: 1.5rem;
    }

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .badge.bg-login {
        background-color: #5e72e4 !important;
        color: white !important;
    }
<<<<<<< HEAD
    .select2-container {
        width: 100% !important;
    }
=======

    .select2-container {
        width: 100% !important;
    }

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
    .back-button {
        margin-left: 15px;
    }
</style>
@endpush

@section('content')
<div class="logs-container">
    <div class="page-header">
        <div class="page-title-container">
<<<<<<< HEAD
            <h1 class="page-title">Activity Logs</h1>
=======
            <h1 class="page-title mb-3">Activity Logs</h1>
>>>>>>> bc89ff9f (Update activity_logs.blade.php)
            <p class="page-subtitle">Track all user activities in the system</p>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-light back-button">
                <i class="mdi mdi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="filter-form">
        <form action="{{ route('admin.activities') }}" method="GET">
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="user_id">User</label>
                    <select name="user_id" id="user_id" class="form-control select2-users">
                        <option value="">All Users</option>
                        @foreach($users as $user)
<<<<<<< HEAD
                            <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->fname }} {{ $user->lname }}
                            </option>
=======
                        <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                            {{ $user->fname }} {{ $user->lname }}
                        </option>
>>>>>>> bc89ff9f (Update activity_logs.blade.php)
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label for="action_type">Action Type</label>
                    <select name="action_type" id="action_type" class="form-control select2-basic">
                        <option value="">All Types</option>
                        @foreach($actionTypes as $type)
<<<<<<< HEAD
                            <option value="{{ $type }}" {{ request('action_type') == $type ? 'selected' : '' }}>
                                {{ $type }}
                            </option>
=======
                        <option value="{{ $type }}" {{ request('action_type') == $type ? 'selected' : '' }}>
                            {{ $type }}
                        </option>
>>>>>>> bc89ff9f (Update activity_logs.blade.php)
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label for="action">Action</label>
                    <input type="text" name="action" id="action" class="form-control" value="{{ request('action') }}" placeholder="Filter by action">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="date_from">Date From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-4 form-group">
                    <label for="date_to">Date To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
<<<<<<< HEAD
                <div class="col-md-4 form-group filter-buttons">
=======
                <div class="col-md-4 form-group filter-buttons mt-3">
>>>>>>> bc89ff9f (Update activity_logs.blade.php)
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.activities') }}" class="btn btn-secondary ml-2">
                        <i class="mdi mdi-refresh"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Activity Logs Table -->
    <div class="content-card">
<<<<<<< HEAD
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h6 class="card-subtitle">System</h6>
                <h3 class="card-title">Activity Logs</h3>
=======
        <div class="card-header d-flex justify-content-between align-items-center rounded-top">
            <div>
                <h6 class="card-subtitle mt-1 mb-2">System</h6>
                <h3 class="card-title mt-1">Activity Logs</h3>
>>>>>>> bc89ff9f (Update activity_logs.blade.php)
            </div>
            <div>
                <span class="badge bg-white text-primary">Total: {{ $activities->total() }}</span>
            </div>
        </div>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Action Type</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Time</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($activities as $activity)
                    <tr>
                        <td>{{ $activity->id }}</td>
                        <td>{{ $activity->user_name }}</td>
                        <td>
                            @php
<<<<<<< HEAD
                                $actionType = $activity->action_type;
                                
                                $badgeClass = 'bg-secondary';
                                
                                switch($actionType) {
                                    case 'Create':
                                        $badgeClass = 'bg-success';
                                        break;
                                    case 'Update':
                                        $badgeClass = 'bg-primary';
                                        break;
                                    case 'Delete':
                                        $badgeClass = 'bg-danger';
                                        break;
                                    case 'Upload':
                                        $badgeClass = 'bg-info';
                                        break;
                                    case 'View':
                                        $badgeClass = 'bg-light text-dark';
                                        break;
                                    case 'Submit':
                                        $badgeClass = 'bg-warning text-dark';
                                        break;
                                    case 'Scopus':
                                        $badgeClass = 'bg-info';
                                        break;
                                    case 'Paper':
                                        $badgeClass = 'bg-primary';
                                        break;
                                    case 'Login':
                                        $badgeClass = 'bg-login';
                                        break;
                                }
=======
                            $actionType = $activity->action_type;

                            $badgeClass = 'bg-secondary';

                            switch($actionType) {
                            case 'Create':
                            $badgeClass = 'bg-success';
                            break;
                            case 'Update':
                            $badgeClass = 'bg-primary';
                            break;
                            case 'Delete':
                            $badgeClass = 'bg-danger';
                            break;
                            case 'Upload':
                            $badgeClass = 'bg-info';
                            break;
                            case 'View':
                            $badgeClass = 'bg-light text-dark';
                            break;
                            case 'Submit':
                            $badgeClass = 'bg-warning text-dark';
                            break;
                            case 'Scopus':
                            $badgeClass = 'bg-info';
                            break;
                            case 'Paper':
                            $badgeClass = 'bg-primary';
                            break;
                            case 'Login':
                            $badgeClass = 'bg-login';
                            break;
                            }
>>>>>>> bc89ff9f (Update activity_logs.blade.php)
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ $actionType }}</span>
                        </td>
                        <td>{{ str_replace($activity->action_type . ' ', '', $activity->action) }}</td>
                        <td>{{ Str::limit($activity->description, 40) }}</td>
                        <td>{{ $activity->ip_address }}</td>
                        <td>{{ \Carbon\Carbon::parse($activity->created_at)->format('Y-m-d H:i:s') }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#activityModal{{ $activity->id }}">
                                <i class="mdi mdi-information-outline"></i>
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="p-4">
            {{ $activities->withQueryString()->links() }}
        </div>
    </div>
</div>

<!-- Activity Details Modals -->
@foreach($activities as $activity)
@php
<<<<<<< HEAD
    $actionType = $activity->action_type;
    
    $badgeClass = 'bg-secondary';
    
    switch($actionType) {
        case 'Create':
            $badgeClass = 'bg-success';
            break;
        case 'Update':
            $badgeClass = 'bg-primary';
            break;
        case 'Delete':
            $badgeClass = 'bg-danger';
            break;
        case 'Upload':
            $badgeClass = 'bg-info';
            break;
        case 'View':
            $badgeClass = 'bg-light text-dark';
            break;
        case 'Submit':
            $badgeClass = 'bg-warning text-dark';
            break;
        case 'Scopus':
            $badgeClass = 'bg-info';
            break;
        case 'Paper':
            $badgeClass = 'bg-primary';
            break;
        case 'Login':
            $badgeClass = 'bg-login';
            break;
    }
=======
$actionType = $activity->action_type;

$badgeClass = 'bg-secondary';

switch($actionType) {
case 'Create':
$badgeClass = 'bg-success';
break;
case 'Update':
$badgeClass = 'bg-primary';
break;
case 'Delete':
$badgeClass = 'bg-danger';
break;
case 'Upload':
$badgeClass = 'bg-info';
break;
case 'View':
$badgeClass = 'bg-light text-dark';
break;
case 'Submit':
$badgeClass = 'bg-warning text-dark';
break;
case 'Scopus':
$badgeClass = 'bg-info';
break;
case 'Paper':
$badgeClass = 'bg-primary';
break;
case 'Login':
$badgeClass = 'bg-login';
break;
}
>>>>>>> bc89ff9f (Update activity_logs.blade.php)
@endphp
<div class="modal fade" id="activityModal{{ $activity->id }}" tabindex="-1" role="dialog" aria-labelledby="activityModalLabel{{ $activity->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="activityModalLabel{{ $activity->id }}">
                    <span class="badge {{ $badgeClass }}">{{ $actionType }}</span>
                    Activity Details #{{ $activity->id }}
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>User:</strong> {{ $activity->user_name }}</p>
                        <p><strong>Action:</strong> {{ $activity->action }}</p>
                        <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($activity->created_at)->format('Y-m-d H:i:s') }}</p>
<<<<<<< HEAD
                    </div>
                    <div class="col-md-6">
                        <p><strong>IP Address:</strong> {{ $activity->ip_address }}</p>
                        <p><strong>User Agent:</strong> {{ Str::limit($activity->user_agent, 100) }}</p>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <p><strong>Description:</strong></p>
                        <div class="p-3 bg-light rounded">
=======
                        <p><strong>IP Address:</strong> {{ $activity->ip_address }}</p>
                        <p><strong>User Agent:</strong></p>
                    </div>
                </div>
                <div class="mb-3 p-3 bg-light border rounded"
                    style="line-height: 1.5; overflow-wrap: break-word; max-width: 100%; max-height: 200px; font-size: 0.812rem; font-weight: 400; color: #525f7f;">
                    {{ $activity->user_agent }}
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <p><strong>Description:</strong></p>
                        <div class="p-3 bg-light border rounded"
                            style="line-height: 1.5; overflow-wrap: break-word; max-width: 100%; max-height: 200px; font-size: 0.812rem; font-weight: 400; color: #525f7f;">
>>>>>>> bc89ff9f (Update activity_logs.blade.php)
                            {{ $activity->description }}
                        </div>
                    </div>
                </div>
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
        // Initialize Select2 for user dropdown with search
        $('.select2-users').select2({
            placeholder: 'Select a user',
            allowClear: true,
            width: '100%',
            dropdownParent: $('.filter-form')
        });
<<<<<<< HEAD
        
=======

>>>>>>> bc89ff9f (Update activity_logs.blade.php)
        // Initialize Select2 for other dropdowns
        $('.select2-basic').select2({
            width: '100%',
            dropdownParent: $('.filter-form')
        });
    });
</script>
<<<<<<< HEAD
@endpush 
=======
@endpush
>>>>>>> bc89ff9f (Update activity_logs.blade.php)
