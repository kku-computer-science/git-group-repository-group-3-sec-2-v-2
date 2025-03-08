@extends('dashboards.users.layouts.user-dash-layout')

@section('title', 'Dashboard')

<style>
    .dashboard-container {
        padding: 20px;
        background: #f8f9fe;
    }

    .welcome-section {
        background: url('/images/cs-logo.png') no-repeat center;
        background-size: contain;
        height: 300px;
        margin-bottom: 30px;
        position: relative;
    }

    .welcome-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.9);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
    }

    .stat-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        padding: 20px;
        margin-bottom: 20px;
        transition: transform 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 15px;
    }

    .stat-icon i {
        font-size: 24px;
        color: white;
    }

    .stat-title {
        color: #8898aa;
        font-size: 0.875rem;
        text-transform: uppercase;
        margin-bottom: 5px;
    }

    .stat-value {
        color: #32325d;
        font-size: 1.5rem;
        font-weight: 600;
    }

    .stat-subtitle {
        color: #525f7f;
        font-size: 0.875rem;
    }

    .content-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
    }

    .content-card .card-header {
        padding: 20px;
        border-bottom: 1px solid #e9ecef;
    }

    .content-card .card-title {
        margin: 0;
        color: #32325d;
        font-size: 1.25rem;
        font-weight: 600;
    }

    .content-card .card-subtitle {
        color: #8898aa;
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
        color: #8898aa;
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

    .badge {
        padding: 5px 10px;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 5px;
    }

    .bg-primary {
        background: #5e72e4 !important;
    }

    .bg-success {
        background: #2dce89 !important;
    }

    .bg-info {
        background: #11cdef !important;
    }

    .bg-warning {
        background: #fb6340 !important;
    }
</style>

@section('content')
<div class="dashboard-container">
    <!-- Welcome Section -->
    <div class="welcome-section">
        <div class="welcome-overlay">
            <h1 class="mb-3">Research Information Management System</h1>
            <h4 class="text-muted">Welcome, {{ Auth::user()->fname_en }} {{ Auth::user()->lname_en }}</h4>
            <p class="text-muted">{{ $roles->implode(', ') }}</p>
        </div>
    </div>

    @if($user->hasRole('admin'))
    <!-- Admin Stats -->
    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="mdi mdi-account-multiple"></i>
                </div>
                <div class="stat-title">Total Users</div>
                <div class="stat-value">{{ $systemInfo['total_users'] }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="mdi mdi-file-document"></i>
                </div>
                <div class="stat-title">Total Papers</div>
                <div class="stat-value">{{ $systemInfo['total_papers'] }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-info">
                    <i class="mdi mdi-harddisk"></i>
                </div>
                <div class="stat-title">Disk Usage</div>
                <div class="stat-value">{{ $systemInfo['disk_free_space'] }}</div>
                <div class="stat-subtitle">Free of {{ $systemInfo['disk_total_space'] }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-warning">
                    <i class="mdi mdi-information"></i>
                </div>
                <div class="stat-title">System Info</div>
                <div class="stat-value">Laravel {{ $systemInfo['laravel_version'] }}</div>
                <div class="stat-subtitle">PHP {{ $systemInfo['php_version'] }}</div>
            </div>
        </div>
    </div>

    <!-- Activity & Error Logs -->
    <div class="row">
        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Overview</h6>
                        <h3 class="card-title">Recent Activities</h3>
                    </div>
                    <a href="{{ route('admin.activities') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Action</th>
                                <th>Time</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($userActivities as $activity)
                            <tr>
                                <td>{{ $activity->user_name }}</td>
                                <td>{{ Str::limit($activity->action, 30) }}</td>
                                <td>{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#activityModal{{ $activity->id }}">
                                        <i class="mdi mdi-information-outline"></i>
                                    </button>

                                    <!-- Activity Details Modal -->
                                    <div class="modal fade" id="activityModal{{ $activity->id }}" tabindex="-1" role="dialog" aria-labelledby="activityModalLabel{{ $activity->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="activityModalLabel{{ $activity->id }}">Activity Details</h5>
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
                                                            <p><strong>IP Address:</strong> {{ $activity->ip_address }}</p>
                                                            <p><strong>User Agent:</strong> {{ $activity->user_agent }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Description:</strong></p>
                                                            <div class="mb-3 bg-light border rounded"
                                                                style=" line-height: 1.5; overflow-wrap: break-word; max-width: 100%; max-height: 200px;
                                                                        overflow-y: auto; text-align: left; padding: 1rem;">
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
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3">
                    {{ $userActivities->links() }}
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">System</h6>
                        <h3 class="card-title">Error Logs</h3>
                    </div>
                    <a href="{{ route('admin.errors') }}" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Level</th>
                                <th>Message</th>
                                <th>Time</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($errorLogs as $error)
                            <tr>
                                <td>
                                    <span class="badge bg-{{ $error->level == 'error' ? 'danger' : ($error->level == 'warning' ? 'warning' : 'info') }} text-white">
                                        {{ $error->level }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($error->message, 30) }}</td>
                                <td>{{ \Carbon\Carbon::parse($error->created_at)->diffForHumans() }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#errorModal{{ $error->id }}">
                                        <i class="mdi mdi-information-outline"></i>
                                    </button>

                                    <!-- Error Details Modal -->
                                    <div class="modal fade" id="errorModal{{ $error->id }}" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel{{ $error->id }}" aria-hidden="true">
                                        <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="errorModalLabel{{ $error->id }}">Error Details</h5>
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
                                                            <p><strong>Message:</strong></p>
                                                            <div class="mb-3 bg-light border rounded"
                                                                style=" line-height: 1.5; overflow-wrap: break-word; max-width: 100%; max-height: 200px;
                                                                        overflow-y: auto; text-align: left; padding: 1rem;">
                                                                {{ $error->message }}
                                                            </div>
                                                        </div>
                                                    </div>
                                                    @if($error->stack_trace)
                                                    <div class="row mt-3">
                                                        <div class="col-12">
                                                            <p><strong>Stack Trace:</strong></p>
                                                            <div class="mb-3 bg-light border rounded" style="max-height: 200px; overflow-y: auto;">
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
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($user->hasRole('researcher'))
    <!-- Researcher Content -->
    <div class="row">
        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header">
                    <h6 class="card-subtitle">Research</h6>
                    <h3 class="card-title">Recent Papers</h3>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($papers as $paper)
                            <tr>
                                <td>{{ $paper->title }}</td>
                                <td>{{ \Carbon\Carbon::parse($paper->created_at)->format('Y-m-d') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header">
                    <h6 class="card-subtitle">Projects</h6>
                    <h3 class="card-title">Research Projects</h3>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Project Name</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($research_projects as $project)
                            <tr>
                                <td>{{ $project->name }}</td>
                                <td>
                                    <span class="badge bg-{{ $project->status == 'Completed' ? 'success' : 'primary' }} text-white">
                                        {{ $project->status }}
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    @if($user->hasRole('student'))
    <!-- Student Content -->
    <div class="row">
        <div class="col-12">
            <div class="content-card">
                <div class="card-header">
                    <h6 class="card-subtitle">Education</h6>
                    <h3 class="card-title">Enrolled Courses</h3>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Course Code</th>
                                <th>Course Name</th>
                                <th>Credits</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($courses as $course)
                            <tr>
                                <td>{{ $course->code }}</td>
                                <td>{{ $course->name }}</td>
                                <td>{{ $course->credits }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
