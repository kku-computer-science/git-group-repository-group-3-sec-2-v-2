@extends('dashboards.users.layouts.user-dash-layout')

@section('title', 'System Information')

<style>
    .system-container {
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
    .info-section {
        padding: 20px;
    }
    .info-item {
        margin-bottom: 15px;
        padding-bottom: 15px;
        border-bottom: 1px solid #e9ecef;
    }
    .info-item:last-child {
        border-bottom: none;
    }
    .info-label {
        font-weight: 600;
        color: #8898aa;
    }
    .info-value {
        font-weight: 400;
        color: #525f7f;
    }
    .stat-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 0 15px rgba(0,0,0,0.1);
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
    .bg-primary { background: #5e72e4 !important; }
    .bg-success { background: #2dce89 !important; }
    .bg-info { background: #11cdef !important; }
    .bg-warning { background: #fb6340 !important; }
    .bg-danger { background: #f5365c !important; }
    .bg-purple { background: #8965e0 !important; }
</style>

@section('content')
<div class="system-container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>System Information</h1>
        <a href="{{ route('dashboard') }}" class="btn btn-secondary">Back to Dashboard</a>
    </div>

    <!-- System Stats -->
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
                    <i class="mdi mdi-database"></i>
                </div>
                <div class="stat-title">Database Size</div>
                <div class="stat-value">{{ $systemInfo['database_size'] }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-warning">
                    <i class="mdi mdi-harddisk"></i>
                </div>
                <div class="stat-title">Disk Usage</div>
                <div class="stat-value">{{ $systemInfo['disk_free_space'] }}</div>
                <div class="stat-subtitle">Free of {{ $systemInfo['disk_total_space'] }}</div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-danger">
                    <i class="mdi mdi-alert-circle"></i>
                </div>
                <div class="stat-title">Error Logs</div>
                <div class="stat-value">{{ $systemInfo['error_logs_count'] }}</div>
                <div class="stat-subtitle">
                    <a href="{{ route('admin.errors') }}" class="text-primary">View All</a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-purple">
                    <i class="mdi mdi-history"></i>
                </div>
                <div class="stat-title">Activity Logs</div>
                <div class="stat-value">{{ $systemInfo['activity_logs_count'] }}</div>
                <div class="stat-subtitle">
                    <a href="{{ route('admin.activities') }}" class="text-primary">View All</a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="mdi mdi-memory"></i>
                </div>
                <div class="stat-title">Memory Usage</div>
                <div class="stat-value">{{ $systemInfo['memory_usage'] }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="mdi mdi-gauge"></i>
                </div>
                <div class="stat-title">Server Load</div>
                <div class="stat-value">{{ $systemInfo['server_load'][0] }}</div>
                <div class="stat-subtitle">1 min avg</div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="content-card">
        <div class="card-header">
            <h6 class="card-subtitle">Details</h6>
            <h3 class="card-title">System Information</h3>
        </div>
        <div class="info-section">
            <div class="row">
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label">PHP Version</div>
                        <div class="info-value">{{ $systemInfo['php_version'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Laravel Version</div>
                        <div class="info-value">{{ $systemInfo['laravel_version'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Server Software</div>
                        <div class="info-value">{{ $systemInfo['server_software'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Database Name</div>
                        <div class="info-value">{{ $systemInfo['database_name'] }}</div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="info-item">
                        <div class="info-label">Server Load</div>
                        <div class="info-value">
                            1 min: {{ $systemInfo['server_load'][0] }}, 
                            5 min: {{ $systemInfo['server_load'][1] }}, 
                            15 min: {{ $systemInfo['server_load'][2] }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Memory Usage</div>
                        <div class="info-value">{{ $systemInfo['memory_usage'] }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Disk Space</div>
                        <div class="info-value">
                            Free: {{ $systemInfo['disk_free_space'] }} / 
                            Total: {{ $systemInfo['disk_total_space'] }}
                        </div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Database Size</div>
                        <div class="info-value">{{ $systemInfo['database_size'] }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 