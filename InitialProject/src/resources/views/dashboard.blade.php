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

    /* Pagination Styles */
    .pagination {
        justify-content: center;
        margin: 0;
    }
    
    .page-link {
        color: #5e72e4;
        border: 1px solid #dee2e6;
        margin: 0 2px;
    }
    
    .page-link:hover {
        color: #233dd2;
        background-color: #e9ecef;
        border-color: #dee2e6;
    }
    
    .page-item.active .page-link {
        background-color: #5e72e4;
        border-color: #5e72e4;
    }
    
    .pagination-info {
        color: #8898aa;
        font-size: 0.875rem;
        text-align: center;
        margin-bottom: 10px;
    }

    /* Add new styles for security monitoring */
    .security-alert {
        padding: 10px 15px;
        border-left: 4px solid;
        margin-bottom: 10px;
    }

    .security-alert.high {
        border-left-color: #dc3545;
        background-color: #fff5f5;
    }

    .security-alert.medium {
        border-left-color: #ffc107;
        background-color: #fff9e6;
    }

    .security-alert.low {
        border-left-color: #17a2b8;
        background-color: #f0f9fb;
    }

    .security-icon {
        font-size: 20px;
        margin-right: 10px;
    }

    .threat-level {
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: bold;
    }

    .threat-level.high {
        background-color: #dc3545;
        color: white;
    }

    .threat-level.medium {
        background-color: #ffc107;
        color: black;
    }

    .threat-level.low {
        background-color: #17a2b8;
        color: white;
    }

    .security-stats {
        display: flex;
        justify-content: space-between;
        padding: 15px;
        background: #f8f9fa;
        border-radius: 8px;
        margin-bottom: 20px;
    }

    .security-stat-item {
        text-align: center;
    }

    .security-stat-value {
        font-size: 24px;
        font-weight: bold;
        color: #32325d;
    }

    .security-stat-label {
        font-size: 14px;
        color: #8898aa;
    }

    .security-stat-card {
        padding: 20px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        margin-bottom: 15px;
        transition: transform 0.2s;
    }

    .security-stat-card:hover {
        transform: translateY(-5px);
    }

    .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
    }

    .stat-icon i {
        font-size: 24px;
        color: white;
    }

    .stat-details {
        flex-grow: 1;
    }

    .stat-value {
        font-size: 24px;
        font-weight: bold;
        margin: 0;
    }

    .stat-label {
        margin: 0;
        color: #6c757d;
        font-size: 14px;
    }

    .bg-danger-light {
        background-color: rgba(220, 53, 69, 0.1);
    }

    .bg-warning-light {
        background-color: rgba(255, 193, 7, 0.1);
    }

    .bg-info-light {
        background-color: rgba(23, 162, 184, 0.1);
    }

    .bg-success-light {
        background-color: rgba(40, 167, 69, 0.1);
    }

    .security-icon {
        font-size: 18px;
        margin-right: 5px;
        vertical-align: middle;
    }

    .badge {
        padding: 5px 10px;
        font-weight: 500;
    }

    .btn-group .btn {
        margin-left: 5px;
    }

    .table td {
        vertical-align: middle;
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

    <!-- Security Monitoring Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Security</h6>
                        <h3 class="card-title">Security Monitoring Dashboard</h3>
                    </div>
                    <div class="btn-group">
                        <a href="{{ route('admin.security.events') }}" class="btn btn-primary btn-sm">
                            <i class="mdi mdi-format-list-bulleted"></i> View Events
                        </a>
                        <a href="{{ route('admin.security.blocked-ips') }}" class="btn btn-danger btn-sm">
                            <i class="mdi mdi-shield-lock"></i> Manage Blocked IPs
                        </a>
                        <button class="btn btn-secondary btn-sm" onclick="refreshSecurityData()">
                            <i class="mdi mdi-refresh"></i> Refresh
                        </button>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Security Stats -->
                    <div class="security-stats mb-4">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="security-stat-card bg-danger-light">
                                    <div class="stat-icon bg-danger">
                                        <i class="mdi mdi-shield-alert"></i>
                                    </div>
                                    <div class="stat-details">
                                        <h3 class="stat-value text-danger">{{ $securityStats['failed_logins'] ?? 0 }}</h3>
                                        <p class="stat-label">Failed Logins (24h)</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="security-stat-card bg-warning-light">
                                    <div class="stat-icon bg-warning">
                                        <i class="mdi mdi-ip-network"></i>
                                    </div>
                                    <div class="stat-details">
                                        <h3 class="stat-value text-warning">{{ $securityStats['suspicious_ips'] ?? 0 }}</h3>
                                        <p class="stat-label">Suspicious IPs</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="security-stat-card bg-info-light">
                                    <div class="stat-icon bg-info">
                                        <i class="mdi mdi-shield-check"></i>
                                    </div>
                                    <div class="stat-details">
                                        <h3 class="stat-value text-info">{{ $securityStats['blocked_attempts'] ?? 0 }}</h3>
                                        <p class="stat-label">Blocked Attempts</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="security-stat-card bg-success-light">
                                    <div class="stat-icon bg-success">
                                        <i class="mdi mdi-chart-line"></i>
                                    </div>
                                    <div class="stat-details">
                                        <h3 class="stat-value text-success">{{ $securityStats['total_monitoring'] ?? 0 }}</h3>
                                        <p class="stat-label">Monitored Events</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Security Events -->
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Event Type</th>
                                    <th>User/IP</th>
                                    <th>Details</th>
                                    <th>Threat Level</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($securityEvents ?? [] as $event)
                                <tr>
                                    <td>{{ $event->created_at->diffForHumans() }}</td>
                                    <td>
                                        <i class="mdi {{ $event->icon_class }} security-icon"></i>
                                        {{ ucwords(str_replace('_', ' ', $event->event_type)) }}
                                    </td>
                                    <td>
                                        @if($event->user_id)
                                            {{ $event->user->name ?? 'Unknown' }}<br>
                                            <small class="text-muted">{{ $event->ip_address }}</small>
                                        @else
                                            {{ $event->ip_address }}
                                        @endif
                                    </td>
                                    <td>{{ Str::limit($event->details, 50) }}</td>
                                    <td>
                                        <span class="badge badge-{{ $event->threat_level === 'high' ? 'danger' : ($event->threat_level === 'medium' ? 'warning' : 'info') }}">
                                            {{ ucfirst($event->threat_level) }}
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#securityModal{{ $event->id }}">
                                            <i class="mdi mdi-information-outline"></i>
                                        </button>
                                        @if($event->threat_level === 'high' && !in_array($event->ip_address, Cache::get('blocked_ips', [])))
                                        <button type="button" class="btn btn-sm btn-danger block-ip-btn" 
                                                data-ip="{{ $event->ip_address }}">
                                            <i class="mdi mdi-shield-lock"></i>
                                        </button>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add the security event modals after the table section -->
    @foreach($securityEvents ?? [] as $event)
    <div class="modal fade" id="securityModal{{ $event->id }}" tabindex="-1" role="dialog" aria-labelledby="securityModalLabel{{ $event->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="securityModalLabel{{ $event->id }}">Security Event Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Event Type:</strong> {{ ucwords(str_replace('_', ' ', $event->event_type)) }}</p>
                            <p><strong>Time:</strong> {{ $event->created_at }}</p>
                            <p><strong>IP Address:</strong> {{ $event->ip_address }}</p>
                            <p><strong>User Agent:</strong> {{ $event->user_agent ?? 'N/A' }}</p>
                            <p><strong>Location:</strong> {{ $event->location ?? 'Unknown' }}</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Request Details:</strong></p>
                            <div class="bg-light p-3 rounded">
                                <pre class="mb-0">{{ json_encode($event->request_details ?? [], JSON_PRETTY_PRINT) }}</pre>
                            </div>
                            @if(isset($event->additional_data) && $event->additional_data)
                            <p class="mt-3"><strong>Additional Data:</strong></p>
                            <div class="bg-light p-3 rounded">
                                <pre class="mb-0">{{ json_encode($event->additional_data, JSON_PRETTY_PRINT) }}</pre>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    @if($event->threat_level === 'high' && !in_array($event->ip_address, Cache::get('blocked_ips', [])))
                    <button type="button" class="btn btn-danger block-ip-btn" data-ip="{{ $event->ip_address }}">
                        Block IP
                    </button>
                    @endif
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    <!-- Activity & Error Logs -->
    <div class="row">
        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Overview</h6>
                        <h3 class="card-title">Recent Activities</h3>
                    </div>
                    <div class="d-flex align-items-center">
                        @if($userActivities instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <span class="badge bg-white text-primary mr-3">Total: {{ $userActivities->total() }}</span>
                        @else
                            <span class="badge bg-white text-primary mr-3">Total: {{ count($userActivities) }}</span>
                        @endif
                        <a href="{{ route('admin.activities') }}" class="btn btn-sm btn-primary">View All</a>
                    </div>
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
                                <td>{{ $activity->user_name ?? 'Unknown' }}</td>
                                <td>
                                    @php
                                        $action = $activity->action ?? '';
                                    @endphp
                                    {{ Str::limit($action, 30) }}
                                </td>
                                <td>{{ isset($activity->created_at) && $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->diffForHumans() : 'N/A' }}</td>
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
                                                            <p><strong>User:</strong> {{ $activity->user_name ?? 'Unknown' }}</p>
                                                            <p><strong>Action:</strong> {{ $activity->action ?? 'N/A' }}</p>
                                                            <p><strong>Time:</strong> {{ isset($activity->created_at) && $activity->created_at ? \Carbon\Carbon::parse($activity->created_at)->format('Y-m-d H:i:s') : 'N/A' }}</p>
                                                            <p><strong>IP Address:</strong> {{ $activity->ip_address ?? 'N/A' }}</p>
                                                            <p><strong>User Agent:</strong> {{ $activity->user_agent ?? 'N/A' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Description:</strong></p>
                                                            <div class="mb-3 bg-light border rounded"
                                                                style=" line-height: 1.5; overflow-wrap: break-word; max-width: 100%; max-height: 200px;
                                                                        overflow-y: auto; text-align: left; padding: 1rem;">
                                                                {{ $activity->description ?? 'No description available' }}
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
                @if($userActivities instanceof \Illuminate\Pagination\LengthAwarePaginator && $userActivities->hasPages())
                <div class="p-4">
                    <div class="pagination-info">
                        Showing {{ $userActivities->firstItem() }}-{{ $userActivities->lastItem() }} of {{ $userActivities->total() }} items
                    </div>
                    {{ $userActivities->links() }}
                </div>
                @endif
            </div>
        </div>

        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle">System</h6>
                        <h3 class="card-title">Error Logs</h3>
                    </div>
                    <div class="d-flex align-items-center">
                        @if($errorLogs instanceof \Illuminate\Pagination\LengthAwarePaginator)
                            <span class="badge bg-white text-primary mr-3">Total: {{ $errorLogs->total() }}</span>
                        @else
                            <span class="badge bg-white text-primary mr-3">Total: {{ count($errorLogs) }}</span>
                        @endif
                        <a href="{{ route('admin.errors') }}" class="btn btn-sm btn-primary">View All</a>
                    </div>
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
                                    @php
                                        $level = $error->level ?? 'unknown';
                                    @endphp
                                    <span class="badge bg-{{ $level === 'error' ? 'danger' : ($level === 'warning' ? 'warning' : 'info') }} text-white">
                                        {{ $level }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($error->message ?? '', 40) }}</td>
                                <td>{{ isset($error->created_at) && $error->created_at ? \Carbon\Carbon::parse($error->created_at)->diffForHumans() : 'N/A' }}</td>
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
                                                            <p><strong>Level:</strong> <span class="badge bg-{{ $level === 'error' ? 'danger' : ($level === 'warning' ? 'warning' : 'info') }} text-white">{{ $level }}</span></p>
                                                            <p><strong>User:</strong> {{ $error->user_name ?? 'N/A' }}</p>
                                                            <p><strong>Time:</strong> {{ isset($error->created_at) && $error->created_at ? \Carbon\Carbon::parse($error->created_at)->format('Y-m-d H:i:s') : 'N/A' }}</p>
                                                            <p><strong>IP Address:</strong> {{ $error->ip_address ?? 'N/A' }}</p>
                                                            <p><strong>URL:</strong> {{ $error->url ?? 'N/A' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Message:</strong></p>
                                                            <div class="mb-3 bg-light border rounded p-3" style="overflow-y: auto; max-height: 150px;">
                                                                {{ $error->message ?? 'No message available' }}
                                                            </div>
                                                            
                                                            @if(isset($error->stack_trace) && $error->stack_trace)
                                                            <p><strong>Stack Trace:</strong></p>
                                                            <div class="mb-3 bg-light border rounded p-3" style="overflow-y: auto; max-height: 150px; font-family: monospace; font-size: 0.8rem;">
                                                                <pre>{{ $error->stack_trace }}</pre>
                                                            </div>
                                                            @endif
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
                @if($errorLogs instanceof \Illuminate\Pagination\LengthAwarePaginator && $errorLogs->hasPages())
                <div class="p-4">
                    <div class="pagination-info">
                        Showing {{ $errorLogs->firstItem() }}-{{ $errorLogs->lastItem() }} of {{ $errorLogs->total() }} items
                    </div>
                    {{ $errorLogs->links() }}
                </div>
                @endif
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

<!-- Add JavaScript for Security Dashboard -->
<script>
$(document).ready(function() {
    console.log('Dashboard initialized');
    
    // Add event handlers for security buttons
    $('.block-ip-btn').on('click', function() {
        var ip = $(this).data('ip');
        blockIP(ip);
    });
});

function refreshSecurityData() {
    location.reload();
}

function blockIP(ip) {
    if (!confirm('Are you sure you want to block IP: ' + ip + '?')) {
        return;
    }
    
    console.log('Blocking IP:', ip);
    
    // Find and disable all buttons for this IP
    var buttons = $('.block-ip-btn[data-ip="' + ip + '"]');
    buttons.prop('disabled', true);
    buttons.html('<i class="mdi mdi-loading mdi-spin"></i>');
    
    // Create form data
    var formData = new FormData();
    formData.append('ip', ip);
    formData.append('reason', 'Blocked from admin dashboard');
    formData.append('threat_score', 8);
    formData.append('_token', $('meta[name="csrf-token"]').attr('content'));
    
    // Use jQuery AJAX
    $.ajax({
        url: '/admin/security/block-ip',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            console.log('Block IP response:', response);
            
            if (response.success) {
                alert('IP has been blocked successfully');
                location.reload();
            } else {
                alert('Failed to block IP: ' + (response.message || 'Unknown error'));
                resetButtons(buttons);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error blocking IP:', error);
            alert('An error occurred while blocking the IP: ' + error);
            resetButtons(buttons);
        }
    });
}

function resetButtons(buttons) {
    buttons.prop('disabled', false);
    buttons.each(function() {
        if ($(this).hasClass('btn-sm')) {
            $(this).html('<i class="mdi mdi-shield-lock"></i>');
        } else {
            $(this).text('Block IP');
        }
    });
}
</script>
@endsection
