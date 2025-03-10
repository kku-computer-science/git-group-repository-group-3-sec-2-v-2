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

    /* New styles for enhanced security monitoring */
    .chart-container {
        position: relative;
        height: 250px;
        margin-bottom: 20px;
    }

    .security-dashboard-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 20px;
        overflow: hidden;
    }

    .security-dashboard-card .card-header {
        background-color: #f8f9fe;
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
    }

    .security-dashboard-card .card-body {
        padding: 20px;
    }

    .security-dashboard-card .card-title {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: #32325d;
    }

    .security-dashboard-card .card-subtitle {
        font-size: 0.8rem;
        color: #8898aa;
    }

    .security-number-display {
        text-align: center;
        padding: 20px 0;
    }

    .security-number-display .number {
        font-size: 3rem;
        font-weight: 700;
        color: #5e72e4;
        line-height: 1;
    }

    .security-number-display .label {
        font-size: 0.9rem;
        color: #8898aa;
        margin-top: 10px;
    }

    .security-table-container {
        max-height: 300px;
        overflow-y: auto;
    }

    .search-filter-container {
        padding: 10px;
        background-color: #f8f9fe;
        border-radius: 5px;
        margin-bottom: 15px;
    }

    /* Security Dashboard Styles */
    .security-dashboard-card {
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        margin-bottom: 20px;
        overflow: hidden;
    }
    
    .security-dashboard-card .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #eee;
        padding: 15px 20px;
    }
    
    .security-dashboard-card .card-header h6 {
        margin: 0;
        font-weight: 600;
        font-size: 16px;
    }
    
    .security-dashboard-card .card-header .card-subtitle {
        color: #6c757d;
        font-size: 13px;
        margin-top: 5px;
    }
    
    .security-dashboard-card .card-body {
        padding: 20px;
    }
    
    .chart-container {
        position: relative;
        height: 300px;
        margin-bottom: 10px;
    }
    
    .chart-loader {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(255, 255, 255, 0.8);
        z-index: 10;
        font-size: 16px;
    }
    
    .chart-loader i {
        margin-right: 10px;
        font-size: 24px;
        color: #3f51b5;
    }
    
    .security-stats .stat-card {
        background: #fff;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .security-stat-card {
        display: flex;
        align-items: center;
        background: #fff;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .security-stat-card .stat-icon {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        flex-shrink: 0;
    }
    
    .security-stat-card .stat-icon i {
        font-size: 24px;
        color: #fff;
    }
    
    .security-stat-card .stat-details {
        flex-grow: 1;
    }
    
    .security-stat-card .stat-value {
        font-size: 24px;
        font-weight: 600;
        margin: 0;
    }
    
    .security-stat-card .stat-label {
        margin: 0;
        color: #6c757d;
        font-size: 14px;
    }
    
    .search-filter-container {
        margin-bottom: 20px;
    }
    
    .security-table-container {
        max-height: 400px;
        overflow-y: auto;
    }
    
    .bg-danger-light {
        background-color: rgba(255, 99, 132, 0.1);
    }
    
    .bg-warning-light {
        background-color: rgba(255, 206, 86, 0.1);
    }
    
    .bg-info-light {
        background-color: rgba(54, 162, 235, 0.1);
    }
    
    .bg-success-light {
        background-color: rgba(75, 192, 192, 0.1);
    }
    
    .security-icon {
        margin-right: 5px;
    }
    
    /* Responsive adjustments */
    @media (max-width: 767px) {
        .chart-container {
            height: 250px;
        }
        
        .security-stat-card {
            padding: 10px;
        }
        
        .security-stat-card .stat-icon {
            width: 40px;
            height: 40px;
        }
        
        .security-stat-card .stat-value {
            font-size: 20px;
        }
    }

    /* Additional styles for gauge charts */
    .gauge-row {
        margin-top: 20px;
    }
    
    /* Override Chart.js styles if needed */
    canvas#cpuGauge, canvas#memoryGauge, canvas#diskGauge {
        transform: translateY(-40px); /* Adjust positioning to show semi-circle */
    }
    
    /* Responsive styles for gauge charts */
    @media (max-width: 767px) {
        .gauge-container {
            height: 150px;
        }
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
    <!-- Enhanced Security Monitoring Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Security</h6>
                        <h3 class="card-title">Security & System Monitoring Dashboard</h3>
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
                                        <h3 class="stat-value text-danger" data-stat-type="failed_logins">{{ $securityStats['failed_logins'] ?? 0 }}</h3>
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
                                        <h3 class="stat-value text-warning" data-stat-type="suspicious_ips">{{ $securityStats['suspicious_ips'] ?? 0 }}</h3>
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
                                        <h3 class="stat-value text-info" data-stat-type="blocked_attempts">{{ $securityStats['blocked_attempts'] ?? 0 }}</h3>
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
                                        <h3 class="stat-value text-success" data-stat-type="total_monitoring">{{ $securityStats['total_monitoring'] ?? 0 }}</h3>
                                        <p class="stat-label">Monitored Events</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Incident & Threat Detection Logs -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">Incident & Threat Detection Logs</h5>
                        </div>
                        <div class="col-md-12">
                            <div class="security-dashboard-card">
                                <div class="card-header">
                                    <h6 class="card-title">Latest Security Events</h6>
                                    <div class="card-subtitle">Real-time security events</div>
                                </div>
                                <div class="card-body">
                                    <div class="search-filter-container">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <input type="text" class="form-control" id="securityEventSearch" placeholder="Search events...">
                                            </div>
                                            <div class="col-md-3">
                                                <select class="form-control" id="securityEventTypeFilter">
                                                    <option value="">All Event Types</option>
                                                    <option value="failed_login">Failed Login</option>
                                                    <option value="brute_force_attempt">Brute Force</option>
                                                    <option value="sql_injection_attempt">SQL Injection</option>
                                                    <option value="xss_attempt">XSS Attempt</option>
                                                    <option value="unauthorized_access">Unauthorized Access</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <select class="form-control" id="securityThreatLevelFilter">
                                                    <option value="">All Threat Levels</option>
                                                    <option value="high">High</option>
                                                    <option value="medium">Medium</option>
                                                    <option value="low">Low</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="security-table-container">
                                        <table class="table" id="securityEventsTable">
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
                                                <tr data-event-type="{{ $event->event_type }}" data-threat-level="{{ $event->threat_level }}">
                                                    <td>{{ $event->created_at ? \Carbon\Carbon::parse($event->created_at)->diffForHumans() : 'N/A' }}</td>
                                                    <td>
                                                        <i class="mdi {{ $event->icon_class }} security-icon"></i>
                                                        {{ ucwords(str_replace('_', ' ', $event->event_type)) }}
                                                    </td>
                                                    <td>
                                                        @if($event->user_id)
                                                            @php
                                                                $username = 'Unknown';
                                                                if (isset($event->user)) {
                                                                    $user = $event->user;
                                                                    if ($user->fname_en && $user->lname_en) {
                                                                        $username = $user->fname_en . ' ' . $user->lname_en;
                                                                    } elseif ($user->fname_th && $user->lname_th) {
                                                                        $username = $user->fname_th . ' ' . $user->lname_th;
                                                                    } elseif ($user->email) {
                                                                        $username = $user->email;
                                                                    }
                                                                }
                                                            @endphp
                                                            {{ $username }}<br>
                                                            <small class="text-muted">{{ $event->ip_address }}</small>
                                                        @else
                                                            {{ $event->ip_address }}
                                                        @endif
                                                    </td>
                                                    <td>{{ Str::limit($event->details ?? 'No details available', 50) }}</td>
                                                    <td>
                                                        <span class="badge badge-{{ $event->threat_level === 'high' ? 'danger' : ($event->threat_level === 'medium' ? 'warning' : 'info') }}">
                                                            {{ ucfirst($event->threat_level) }}
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <button type="button" class="btn btn-sm btn-info view-security-event-btn" data-event-id="{{ $event->id }}">
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

                    <!-- User Authentication Monitoring -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">User Authentication Monitoring</h5>
                        </div>
                        <div class="col-md-12">
                            <div class="security-dashboard-card">
                                <div class="card-header">
                                    <h6 class="card-title">Failed Logins (Brute Force Indicator)</h6>
                                    <div class="card-subtitle">Failed login attempts per user (Last 24 hours)</div>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="failedLoginsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- API & Request Monitoring -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">Blocked Requests</h5>
                        </div>
                        <div class="col-md-12">
                            <div class="security-dashboard-card">
                                <div class="card-header">
                                    <h6 class="card-title">Blocked Requests (WAF Rules)</h6>
                                    <div class="card-subtitle">Types of blocked requests</div>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="blockedRequestsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Stats -->
    <div class="row">
        <div class="col-12 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0">System Overview</h4>
                <a href="{{ route('admin.system') }}" class="btn btn-primary btn-sm">
                    <i class="mdi mdi-information-outline mr-1"></i> View All System Info
                </a>
            </div>
        </div>
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
        <!-- Recent Activities Section -->
        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Overview</h6>
                        <h3 class="card-title">Recent Activities</h3>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-white text-primary mr-3">Showing: {{ count($userActivities) }} of {{ $totalActivities }}</span>
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
                                                                style="line-height: 1.5; overflow-wrap: break-word; max-width: 100%; max-height: 200px;
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
            </div>
        </div>

        <!-- Error Logs Section -->
        <div class="col-lg-6">
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">System</h6>
                        <h3 class="card-title">Error Logs</h3>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-white text-primary mr-3">Showing: {{ count($errorLogs) }} of {{ $totalErrorLogs }}</span>
                        <a href="{{ route('admin.errors') }}" class="btn btn-sm btn-primary">View All</a>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Time</th>
                                <th>Level</th>
                                <th>Message</th>
                                <th>Details</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($errorLogs as $error)
                            <tr>
                                <td>{{ isset($error->created_at) && $error->created_at ? \Carbon\Carbon::parse($error->created_at)->diffForHumans() : 'N/A' }}</td>
                                <td>
                                    <span class="badge {{ ($error->level ?? 'info') == 'error' ? 'bg-danger text-white' : (($error->level ?? 'info') == 'warning' ? 'bg-warning text-dark' : 'bg-info text-white') }}">
                                        {{ ucfirst($error->level ?? 'info') }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $message = $error->message ?? 'No message available';
                                    @endphp
                                    {{ Str::limit($message, 30) }}
                                </td>
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
                                                                <span class="badge {{ ($error->level ?? 'info') == 'error' ? 'bg-danger text-white' : (($error->level ?? 'info') == 'warning' ? 'bg-warning text-dark' : 'bg-info text-white') }}">
                                                                    {{ ucfirst($error->level ?? 'info') }}
                                                                </span>
                                                            </p>
                                                            <p><strong>Time:</strong> {{ isset($error->created_at) && $error->created_at ? \Carbon\Carbon::parse($error->created_at)->format('Y-m-d H:i:s') : 'N/A' }}</p>
                                                            <p><strong>File:</strong> {{ $error->file ?? 'Unknown' }}</p>
                                                            <p><strong>Line:</strong> {{ $error->line ?? 'Unknown' }}</p>
                                                            <p><strong>User:</strong> {{ $error->user_name ?? 'Unknown' }}</p>
                                                            <p><strong>IP Address:</strong> {{ $error->ip_address ?? 'Unknown' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Message:</strong></p>
                                                            <div class="mb-3 bg-light border rounded"
                                                                style="line-height: 1.5; overflow-wrap: break-word; max-width: 100%; max-height: 200px;
                                                                        overflow-y: auto; text-align: left; padding: 1rem;">
                                                                {{ $error->message ?? 'No message available' }}
                                                            </div>
                                                            
                                                            <p><strong>Stack Trace:</strong></p>
                                                            <div class="bg-light border rounded"
                                                                style="line-height: 1.5; overflow-wrap: break-word; max-width: 100%; max-height: 200px;
                                                                        overflow-y: auto; text-align: left; padding: 1rem; font-size: 0.8rem;">
                                                                <pre style="white-space: pre-wrap; margin-bottom: 0;">{{ $error->stack_trace ?? 'No stack trace available' }}</pre>
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
                                <td>{{ isset($paper->created_at) && $paper->created_at ? \Carbon\Carbon::parse($paper->created_at)->format('Y-m-d') : 'N/A' }}</td>
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

    // Initialize security event table filters
    initSecurityEventFilters();
    
    // Initialize charts
    console.log('Initializing security charts...');
    initSecurityCharts();
    
    // Add a click handler for the manual refresh button
    $('.refresh-security-btn').on('click', function(e) {
        e.preventDefault();
        refreshSecurityCharts();
    });
});

function initSecurityCharts() {
    console.log('Starting chart initialization');
    
    // Initialize each chart with proper error handling
    initFailedLoginsChart();
    initBlockedRequestsChart();
    
    // Set up auto-refresh for charts
    console.log('Setting up auto-refresh');
    setInterval(refreshSecurityCharts, 300000); // Refresh every 5 minutes instead of every minute
    
    console.log('Chart initialization complete');
}

function initFailedLoginsChart() {
    console.log('Initializing failed logins chart');
    
    var ctx = document.getElementById('failedLoginsChart');
    if (!ctx) {
        console.error('Failed logins chart canvas element not found!');
        return;
    }
    
    // Show loading indicator
    var container = $(ctx).parent();
    container.find('.chart-loader').remove(); // Remove any existing loaders
    container.append('<div class="chart-loader"><i class="mdi mdi-loading mdi-spin"></i> Loading data...</div>');
    
    // Clean up any existing chart to prevent memory leaks
    if (window.failedLoginsChart && typeof window.failedLoginsChart.destroy === 'function') {
        window.failedLoginsChart.destroy();
        window.failedLoginsChart = null;
    }
    
    // Fetch real data from the server
    $.ajax({
        url: '/admin/security/failed-logins-data',
        type: 'GET',
        success: function(response) {
            container.find('.chart-loader').remove();
            console.log('Got failed logins data:', response);
            
            // Check if we have valid data
            if (!response || !response.data || !Array.isArray(response.data.labels) || !Array.isArray(response.data.values)) {
                console.warn('Invalid or missing data format for failed logins chart:', response);
                container.html('<div class="alert alert-warning">Could not load failed login data. Invalid format received.</div>');
                return;
            }
            
            // Use data from response or fallback
            var chartData = {
                labels: response.data.labels.length > 0 ? response.data.labels : ['No failed login attempts'],
                values: response.data.values.length > 0 ? response.data.values : [0]
            };
            
            try {
                // Create the chart
                console.log('Creating failed logins chart with data:', chartData);
                window.failedLoginsChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: chartData.labels,
                        datasets: [{
                            label: 'Failed Login Attempts',
                            data: chartData.values,
                            backgroundColor: 'rgba(255, 99, 132, 0.7)',
                            borderColor: 'rgba(255, 99, 132, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Number of Failed Attempts'
                                }
                            },
                            x: {
                                ticks: {
                                    maxRotation: 45,
                                    minRotation: 45
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                mode: 'index',
                                intersect: false
                            },
                            legend: {
                                display: false
                            }
                        }
                    }
                });
                console.log('Failed logins chart created successfully');
                
                // Add information message if no attempts found
                if (chartData.values.length === 1 && chartData.values[0] === 0) {
                    container.append('<div class="alert alert-info mt-3">No failed login attempts detected in the last 24 hours.</div>');
                }
            } catch (e) {
                console.error('Error creating failed logins chart:', e);
                container.html('<div class="alert alert-danger">Error creating chart: ' + e.message + '</div>');
            }
        },
        error: function(xhr, status, error) {
            container.find('.chart-loader').remove();
            console.error('Error fetching failed logins data:', error);
            container.html('<div class="alert alert-danger">Failed to load login data: ' + error + '</div>');
        }
    });
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

function initSecurityEventFilters() {
    // Search functionality
    $('#securityEventSearch').on('keyup', function() {
        var value = $(this).val().toLowerCase();
        $("#securityEventsTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });
    
    // Event type filter
    $('#securityEventTypeFilter').on('change', function() {
        filterSecurityEvents();
    });
    
    // Threat level filter
    $('#securityThreatLevelFilter').on('change', function() {
        filterSecurityEvents();
    });
}

function filterSecurityEvents() {
    var eventType = $('#securityEventTypeFilter').val();
    var threatLevel = $('#securityThreatLevelFilter').val();
    
    $("#securityEventsTable tbody tr").each(function() {
        var rowEventType = $(this).data('event-type');
        var rowThreatLevel = $(this).data('threat-level');
        
        var showRow = true;
        
        if (eventType && rowEventType !== eventType) {
            showRow = false;
        }
        
        if (threatLevel && rowThreatLevel !== threatLevel) {
            showRow = false;
        }
        
        $(this).toggle(showRow);
    });
}

function initBlockedRequestsChart() {
    var ctx = document.getElementById('blockedRequestsChart');
    if (!ctx) return;
    
    // Fetch real data from the server
    $.ajax({
        url: '/admin/security/blocked-requests-data',
        type: 'GET',
        success: function(response) {
            var data = response.data || {
                labels: ['SQL Injection', 'XSS', 'Brute Force', 'Rate Limit', 'Other'],
                values: [30, 25, 20, 15, 10]
            };
            
            window.blockedRequestsChart = new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: data.labels,
                    datasets: [{
                        data: data.values,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)',
                            'rgba(54, 162, 235, 0.8)',
                            'rgba(255, 206, 86, 0.8)',
                            'rgba(75, 192, 192, 0.8)',
                            'rgba(153, 102, 255, 0.8)'
                        ]
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'right'
                        }
                    }
                }
            });
        },
        error: function(xhr, status, error) {
            console.error('Error fetching blocked requests data:', error);
        }
    });
}

function updateSecurityStats(stats) {
    if (!stats) return;
    
    // Update the security statistics display
    $('.security-stat-value').each(function() {
        var statType = $(this).data('stat-type');
        if (stats[statType] !== undefined) {
            $(this).text(stats[statType]);
        }
    });
}

// Initialize tooltips and popovers
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();
});

// Update blocked requests chart with new data
function updateBlockedRequestsChart(data) {
    if (!window.blockedRequestsChart) {
        initBlockedRequestsChart();
        return;
    }
    
    // Update chart data
    window.blockedRequestsChart.data.labels = data.labels;
    window.blockedRequestsChart.data.datasets[0].data = data.values;
    window.blockedRequestsChart.update();
}

function refreshSecurityCharts() {
    console.log('Refreshing security charts');
    
    // Reinitialize each chart to ensure fresh data
    initFailedLoginsChart();
    initBlockedRequestsChart();
}

function refreshSecurityData() {
    // Show loading indicator
    $('.chart-container').each(function() {
        $(this).find('.chart-loader').remove();
        $(this).append('<div class="chart-loader"><i class="mdi mdi-loading mdi-spin"></i> Loading data...</div>');
    });
    
    console.log('Refreshing security dashboard data...');
    
    // Fetch all charts data at once
    $.ajax({
        url: '/admin/security/dashboard-data',
        type: 'GET',
        success: function(response) {
            $('.chart-loader').remove();
            console.log('Got dashboard data:', response);
            
            // Update security stats
            if (response.securityStats) {
                updateSecurityStats(response.securityStats);
            }
            
            // Refresh all charts
            refreshSecurityCharts();
            
            // Reinitialize modals after AJAX updates
            setTimeout(function() {
                setupSecurityEventModals();
            }, 500);
        },
        error: function(xhr, status, error) {
            $('.chart-loader').remove();
            console.error('Error refreshing security data:', error);
        }
    });
}

function setupSecurityEventModals() {
    // Initialize modals
    if (typeof $.fn.modal === 'function') {
        $('.security-event-modal').modal({
            show: false
        });
    }
    
    // Add click handler for info buttons
    $('.view-security-event-btn').off('click').on('click', function(e) {
        e.preventDefault();
        var eventId = $(this).data('event-id');
        $('#securityModal' + eventId).modal('show');
    });
    
    // Add click handler for close buttons
    $('.close-modal-btn').off('click').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.modal').modal('hide');
    });
    
    // Also handle the X button and backdrop clicks
    $('.modal .close').off('click').on('click', function(e) {
        e.preventDefault();
        $(this).closest('.modal').modal('hide');
    });
}

$(document).ready(function() {
    setupSecurityEventModals();
});
</script>

<!-- Add these modals after the dashboard content -->
@if(isset($securityEvents) && count($securityEvents) > 0)
    @foreach($securityEvents as $event)
    <div class="modal fade security-event-modal" id="securityModal{{ $event->id }}" tabindex="-1" role="dialog" aria-labelledby="securityModalLabel{{ $event->id }}" aria-hidden="true">
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
                            <p><strong>Time:</strong> {{ $event->created_at ? \Carbon\Carbon::parse($event->created_at)->format('Y-m-d H:i:s') : 'N/A' }}</p>
                            <p><strong>Event Type:</strong> {{ ucwords(str_replace('_', ' ', $event->event_type)) }}</p>
                            <p><strong>IP Address:</strong> {{ $event->ip_address }}</p>
                            <p><strong>User:</strong> 
                                @php
                                    $username = 'Unknown';
                                    if (isset($event->user)) {
                                        $user = $event->user;
                                        if ($user->fname_en && $user->lname_en) {
                                            $username = $user->fname_en . ' ' . $user->lname_en;
                                        } elseif ($user->fname_th && $user->lname_th) {
                                            $username = $user->fname_th . ' ' . $user->lname_th;
                                        } elseif ($user->email) {
                                            $username = $user->email;
                                        }
                                    }
                                @endphp
                                {{ $username }}
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Threat Level:</strong> 
                                <span class="badge badge-{{ $event->threat_level === 'high' ? 'danger' : ($event->threat_level === 'medium' ? 'warning' : 'info') }}">
                                    {{ ucfirst($event->threat_level) }}
                                </span>
                            </p>
                            <p><strong>Details:</strong> {{ $event->details }}</p>
                            <p><strong>User Agent:</strong> {{ $event->user_agent ?? 'Unknown' }}</p>
                            <p><strong>Location:</strong> {{ $event->location ?? 'Unknown' }}</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    @if($event->threat_level === 'high' && !in_array($event->ip_address, Cache::get('blocked_ips', [])))
                    <button type="button" class="btn btn-danger block-ip-btn" data-ip="{{ $event->ip_address }}">
                        Block IP
                    </button>
                    @endif
                    <button type="button" class="btn btn-secondary close-modal-btn" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endif
@endsection
