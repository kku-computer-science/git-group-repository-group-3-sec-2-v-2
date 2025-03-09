@extends('dashboards.users.layouts.user-dash-layout')

@section('title', 'System Information')

@push('styles')
<style>
    .system-container {
        padding: 20px;
        background: #f8f9fe;
        min-height: calc(100vh - 100px);
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
    }

    .section-header h4 {
        margin: 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: #32325d;
    }

    .stat-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        padding: 25px;
        margin-bottom: 25px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .stat-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 7px 30px rgba(0,0,0,0.1);
    }

    .stat-icon {
        width: 64px;
        height: 64px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 20px;
        position: relative;
        overflow: hidden;
    }

    .stat-icon i {
        font-size: 28px;
        color: white;
        position: relative;
        z-index: 2;
    }

    .stat-title {
        color: #8898aa;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        font-weight: 600;
    }

    .stat-value {
        color: #2d3748;
        font-size: 1.75rem;
        font-weight: 700;
        margin-bottom: 5px;
        line-height: 1.2;
    }

    .stat-subtitle {
        color: #718096;
        font-size: 0.875rem;
    }

    .content-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 0 20px rgba(0,0,0,0.05);
        margin-bottom: 30px;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .content-card .card-header {
        padding: 20px 25px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
        background: white;
        border-radius: 15px 15px 0 0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header-left {
        display: flex;
        flex-direction: column;
    }

    .content-card .card-subtitle {
        color: #8898aa;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
        font-weight: 600;
    }

    .content-card .card-title {
        color: #2d3748;
        font-size: 1.25rem;
        font-weight: 700;
        margin: 0;
    }

    .info-section {
        padding: 25px;
    }

    .info-item {
        margin-bottom: 20px;
        padding-bottom: 20px;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .info-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }

    .info-label {
        font-weight: 600;
        color: #8898aa;
        font-size: 0.875rem;
        margin-bottom: 5px;
    }

    .info-value {
        font-weight: 500;
        color: #2d3748;
        font-size: 1rem;
        line-height: 1.5;
    }

    .progress {
        height: 8px;
        margin: 15px 0;
        background-color: #edf2f7;
        border-radius: 10px;
        overflow: hidden;
    }

    .progress-bar {
        transition: width 0.6s ease;
        border-radius: 10px;
    }

    .table {
        margin-bottom: 0;
    }

    .table thead th {
        background: #f8f9fe;
        border-top: none;
        border-bottom: 2px solid #e2e8f0;
        color: #8898aa;
        font-size: 0.875rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 15px;
    }

    .table tbody td {
        padding: 15px;
        vertical-align: middle;
        color: #2d3748;
        border-top: 1px solid #e2e8f0;
        font-size: 0.875rem;
    }

    .table tbody tr:hover {
        background-color: #f8f9fe;
    }

    .badge {
        padding: 5px 12px;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.5px;
        border-radius: 7px;
    }

    .bg-error { background-color: #f56565 !important; }
    .bg-warning { background-color: #ed8936 !important; }
    .bg-info { background-color: #4299e1 !important; }
    .bg-emergency { background-color: #1a202c !important; }
    .bg-success { background-color: #48bb78 !important; }
    .bg-primary { background-color: #5e72e4 !important; }
    .bg-purple { background-color: #9f7aea !important; }

    .logs-list {
        max-height: 350px;
        overflow-y: auto;
        scrollbar-width: thin;
        scrollbar-color: #cbd5e0 #edf2f7;
    }

    .logs-list::-webkit-scrollbar {
        width: 6px;
    }

    .logs-list::-webkit-scrollbar-track {
        background: #edf2f7;
        border-radius: 3px;
    }

    .logs-list::-webkit-scrollbar-thumb {
        background-color: #cbd5e0;
        border-radius: 3px;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.05); }
        100% { transform: scale(1); }
    }

    .stat-card:hover .stat-icon {
        animation: pulse 1s infinite;
    }

    .text-primary {
        color: #5e72e4 !important;
        text-decoration: none;
    }

    .text-primary:hover {
        color: #233dd2 !important;
        text-decoration: none;
    }

    /* Responsive Adjustments */
    @media (max-width: 768px) {
        .section-header h4 {
            font-size: 1.1rem;
        }

        .stat-card {
            padding: 20px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
        }

        .stat-icon i {
            font-size: 24px;
        }

        .stat-value {
            font-size: 1.5rem;
        }
    }
</style>
@endpush

@section('content')
<div class="system-container">
    <!-- Section Header -->
    <div class="section-header">
        <h4>System Information</h4>
        <a href="{{ route('dashboard') }}" class="btn btn-primary btn-sm">
            <i class="mdi mdi-arrow-left mr-1"></i> Back to Dashboard
        </a>
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
                <div class="stat-value">{{ $systemInfo['database_size'] ?? 'Unknown' }}</div>
                <div class="stat-subtitle">Tables: {{ $systemInfo['database_tables_count'] ?? $systemInfo['table_count'] ?? 0 }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-warning">
                    <i class="mdi mdi-harddisk"></i>
                </div>
                <div class="stat-title">Disk Usage</div>
                <div class="stat-value">{{ $systemInfo['disk_usage_percent'] ?? '0' }}%</div>
                <div class="progress">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $systemInfo['disk_usage_percent'] ?? '0' }}%" aria-valuenow="{{ $systemInfo['disk_usage_percent'] ?? '0' }}" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="stat-subtitle">{{ $systemInfo['disk_used'] ?? $systemInfo['disk_used_space'] ?? '0 MB' }} used of {{ $systemInfo['disk_total'] ?? $systemInfo['disk_total_space'] ?? 'Unknown' }}</div>
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
                <div class="stat-value">{{ $systemInfo['error_logs_count'] ?? 0 }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-purple">
                    <i class="mdi mdi-history"></i>
                </div>
                <div class="stat-title">Activity Logs</div>
                <div class="stat-value">{{ $systemInfo['activity_logs_count'] ?? 0 }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-primary">
                    <i class="mdi mdi-memory"></i>
                </div>
                <div class="stat-title">Memory Usage</div>
                <div class="stat-value">{{ $systemInfo['memory_usage'] ?? '0 MB' }}</div>
                <div class="stat-subtitle">Peak: {{ $systemInfo['memory_peak_usage'] ?? '0 MB' }}</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="stat-card">
                <div class="stat-icon bg-success">
                    <i class="mdi mdi-server"></i>
                </div>
                <div class="stat-title">Server Load</div>
                <div class="stat-value">{{ $systemInfo['server_load'][0] ?? 'N/A' }}</div>
                <div class="stat-subtitle">
                    5 min: {{ $systemInfo['server_load'][1] ?? 'N/A' }} | 
                    15 min: {{ $systemInfo['server_load'][2] ?? 'N/A' }}
                </div>
            </div>
        </div>
    </div>

    <!-- System Information -->
    <div class="row">
        <div class="col-md-6">
            <div class="content-card">
                <div class="card-header">
                    <div class="card-header-left">
                        <div class="card-subtitle">Details</div>
                        <div class="card-title">System Information</div>
                    </div>
                </div>
                <div class="info-section">
                    <div class="info-item">
                        <div class="info-label">PHP Version</div>
                        <div class="info-value">{{ $systemInfo['php_version'] ?? 'Unknown' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Laravel Version</div>
                        <div class="info-value">{{ $systemInfo['laravel_version'] ?? 'Unknown' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">MySQL Version</div>
                        <div class="info-value">{{ $systemInfo['mysql_version'] ?? 'Unknown' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Server Software</div>
                        <div class="info-value">{{ $systemInfo['server_software'] ?? 'Unknown' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Operating System</div>
                        <div class="info-value">{{ $systemInfo['operating_system'] ?? $systemInfo['os_info'] ?? 'Unknown' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Server Uptime</div>
                        <div class="info-value">{{ $systemInfo['server_uptime'] ?? 'Unknown' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Database Name</div>
                        <div class="info-value">{{ $systemInfo['database_name'] ?? 'Unknown' }}</div>
                    </div>
                    <div class="info-item">
                        <div class="info-label">Timezone</div>
                        <div class="info-value">{{ $systemInfo['timezone'] ?? 'Unknown' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="content-card">
                <div class="card-header">
                    <div class="card-header-left">
                        <div class="card-subtitle">Configuration</div>
                        <div class="card-title">PHP Settings</div>
                    </div>
                </div>
                <div class="info-section">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">Max Execution Time</div>
                                <div class="info-value">{{ isset($systemInfo['php_settings']['max_execution_time']) ? $systemInfo['php_settings']['max_execution_time'] : $systemInfo['max_execution_time'] ?? 'Unknown' }} seconds</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Max Input Time</div>
                                <div class="info-value">{{ isset($systemInfo['php_settings']['max_input_time']) ? $systemInfo['php_settings']['max_input_time'] : $systemInfo['max_input_time'] ?? 'Unknown' }} seconds</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Memory Limit</div>
                                <div class="info-value">{{ isset($systemInfo['php_settings']['memory_limit']) ? $systemInfo['php_settings']['memory_limit'] : $systemInfo['memory_limit'] ?? 'Unknown' }}</div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <div class="info-label">Post Max Size</div>
                                <div class="info-value">{{ isset($systemInfo['php_settings']['post_max_size']) ? $systemInfo['php_settings']['post_max_size'] : $systemInfo['post_max_size'] ?? 'Unknown' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Upload Max Filesize</div>
                                <div class="info-value">{{ isset($systemInfo['php_settings']['upload_max_filesize']) ? $systemInfo['php_settings']['upload_max_filesize'] : $systemInfo['upload_max_filesize'] ?? 'Unknown' }}</div>
                            </div>
                            <div class="info-item">
                                <div class="info-label">Max File Uploads</div>
                                <div class="info-value">{{ isset($systemInfo['php_settings']['max_file_uploads']) ? $systemInfo['php_settings']['max_file_uploads'] : $systemInfo['max_file_uploads'] ?? 'Unknown' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 