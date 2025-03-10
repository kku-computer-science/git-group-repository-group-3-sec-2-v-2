@extends('dashboards.users.layouts.user-dash-layout')

@section('title', 'System Information')

@section('content')
<div class="container">
    <!-- Section Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="text-dark">System Information</h4>
        <a href="{{ route('dashboard') }}" class="btn btn-outline-primary">
            <i class="mdi mdi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <!-- System Stats -->
    <div class="row g-3">
        @foreach([
        ['title' => 'Total Users', 'icon' => 'mdi-account-multiple', 'value' => $systemInfo['total_users'], 'bg' => 'primary'],
        ['title' => 'Total Papers', 'icon' => 'mdi-file-document', 'value' => $systemInfo['total_papers'], 'bg' => 'success'],
        ['title' => 'Database Size', 'icon' => 'mdi-database',
        'value' => ($systemInfo['database_size'] ?? 'Unknown') . ' MB',
        'subvalue' => 'Tables: ' . ($systemInfo['database_tables_count'] ?? $systemInfo['table_count'] ?? 0),
        'bg' => 'info'],
        ['title' => 'Disk Usage', 'icon' => 'mdi-harddisk',
        'value' => ($systemInfo['disk_usage_percent'] ?? '0') . '%',
        'subvalue' => ($systemInfo['disk_used'] ?? '0 GB') . ' used of ' .
        ($systemInfo['disk_total'] ?? 'Unknown GB'),
        'bg' => 'warning']
        ] as $stat)
        <div class="col-xl-3 col-md-6">
            <div class="card text-white bg-{{ $stat['bg'] }} shadow-sm h-100 rounded-2">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="mdi {{ $stat['icon'] }} me-3 fs-3"></i>
                        <div>
                            <h6 class="mb-1">{{ $stat['title'] }}</h6>
                            <h4 class="mb-0 fw-bold">{{ $stat['value'] }}</h4>
                            @if(isset($stat['subvalue']))
                            <small class="d-block mt-1">{{ $stat['subvalue'] }}</small>
                            @endif
                        </div>
                    </div>
                    @if($stat['title'] === 'Disk Usage')
                    <div class="progress mt-3">
                        <div class="progress-bar" role="progressbar"
                            style="width: {{ $systemInfo['disk_usage_percent'] ?? '0' }}%; background-color:rgb(248, 225, 52);"
                            aria-valuenow="{{ $systemInfo['disk_usage_percent'] ?? '0' }}"
                            aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Additional System Stats -->
    <div class="row g-3 mt-3">
        @foreach([
        ['title' => 'Error Logs', 'icon' => 'mdi-alert-circle', 'value' => $systemInfo['error_logs_count'] ?? 0, 'bg' => 'danger'],
        ['title' => 'Activity Logs', 'icon' => 'mdi-history', 'value' => $systemInfo['activity_logs_count'] ?? 0, 'bg' => 'warning'],
        ['title' => 'Memory Usage', 'icon' => 'mdi-memory',
        'value' => $systemInfo['memory_usage'] ?? '0 MB',
        'subvalue' => 'Peak: ' . ($systemInfo['memory_peak_usage'] ?? '0 MB'),
        'bg' => 'primary'],
        ['title' => 'Server Load', 'icon' => 'mdi-server',
        'value' => $systemInfo['server_load'][0] ?? 'N/A',
        'subvalue' => '5 min: ' . ($systemInfo['server_load'][1] ?? 'N/A') .
        ' | 15 min: ' . ($systemInfo['server_load'][2] ?? 'N/A'),
        'bg' => 'success']
        ] as $stat)
        <div class="col-xl-3 col-md-6">
            <div class="card text-white bg-{{ $stat['bg'] }} shadow-sm h-100 rounded-2">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center">
                        <i class="mdi {{ $stat['icon'] }} me-3 fs-3"></i>
                        <div>
                            <h6 class="mb-1">{{ $stat['title'] }}</h6>
                            <h4 class="mb-0 fw-bold">{{ $stat['value'] }}</h4>
                            @if(isset($stat['subvalue']))
                            <small class="d-block mt-1">{{ $stat['subvalue'] }}</small>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- System Information & PHP Settings -->
    <div class="row mt-4">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">System Information</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>PHP Version</span>
                            <strong>{{ $systemInfo['php_version'] ?? 'Unknown' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Laravel Version</span>
                            <strong>{{ $systemInfo['laravel_version'] ?? 'Unknown' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>MySQL Version</span>
                            <strong>{{ $systemInfo['mysql_version'] ?? 'Unknown' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Server Software</span>
                            <strong>{{ $systemInfo['server_software'] ?? 'Unknown' }}</strong>
                        </li>
                        <li class="list-group-item">
                            <span class="d-block fw-bold">Operating System</span>
                            <div class="bg-light p-2 rounded text-break">
                                <pre class="mb-0 text-muted">{{ $systemInfo['operating_system'] ?? 'Unknown' }}</pre>
                            </div>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Server Uptime</span>
                            <strong>{{ $systemInfo['server_uptime'] ?? 'Unknown' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Database Name</span>
                            <strong>{{ $systemInfo['database_name'] ?? 'Unknown' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Timezone</span>
                            <strong>{{ $systemInfo['timezone'] ?? 'Unknown' }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">PHP Settings</div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Max Execution Time</span>
                            <strong>{{ $systemInfo['php_settings']['max_execution_time'] ?? 'Unknown' }} sec</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Max Input Time</span>
                            <strong>{{ $systemInfo['php_settings']['max_input_time'] ?? 'Unknown' }} sec</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Memory Limit</span>
                            <strong>{{ $systemInfo['php_settings']['memory_limit'] ?? 'Unknown' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Post Max Size</span>
                            <strong>{{ $systemInfo['php_settings']['post_max_size'] ?? 'Unknown' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Upload Max Filesize</span>
                            <strong>{{ $systemInfo['php_settings']['upload_max_filesize'] ?? 'Unknown' }}</strong>
                        </li>
                        <li class="list-group-item d-flex justify-content-between">
                            <span>Max File Uploads</span>
                            <strong>{{ $systemInfo['php_settings']['max_file_uploads'] ?? 'Unknown' }}</strong>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
