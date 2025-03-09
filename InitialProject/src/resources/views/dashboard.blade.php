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

    .gauge-container {
        position: relative;
        height: 200px;
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
                    <!-- 1. Security Overview Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">Security Overview</h5>
                        </div>
                        <div class="col-md-4">
                            <div class="security-dashboard-card">
                                <div class="card-header">
                                    <h6 class="card-title">Total Security Alerts</h6>
                                </div>
                                <div class="card-body">
                                    <div class="security-number-display">
                                        <div class="number">{{ $securityStats['total_monitoring'] ?? 0 }}</div>
                                        <div class="label">Total alerts in the system</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="security-dashboard-card">
                                <div class="card-header">
                                    <h6 class="card-title">Blocked IPs</h6>
                                </div>
                                <div class="card-body">
                                    <div class="security-number-display">
                                        <div class="number">{{ $securityStats['suspicious_ips'] ?? 0 }}</div>
                                        <div class="label">Currently blocked IP addresses</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="security-dashboard-card">
                                <div class="card-header">
                                    <h6 class="card-title">Suspicious Login Attempts</h6>
                                </div>
                                <div class="card-body">
                                    <div class="security-number-display">
                                        <div class="number">{{ $securityStats['failed_logins'] ?? 0 }}</div>
                                        <div class="label">Failed login attempts (24h)</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. User Authentication Monitoring -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">User Authentication Monitoring</h5>
                        </div>
                        <div class="col-md-12">
                            <div class="security-dashboard-card">
                                <div class="card-header">
                                    <h6 class="card-title">Failed Logins (Brute Force Indicator)</h6>
                                    <div class="card-subtitle">Failed login attempts per user</div>
                                </div>
                                <div class="card-body">
                                    <div class="chart-container">
                                        <canvas id="failedLoginsChart"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. API & Request Monitoring -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h5 class="mb-3">API & Request Monitoring</h5>
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

                    <!-- 4. Incident & Threat Detection Logs -->
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
                                                                // Avoid querying the database inside a loop - this can cause memory issues
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

                    <!-- 5. Performance Monitoring -->
                    <div class="row">
                        <div class="col-12">
                            <h5 class="mb-3">Performance Monitoring</h5>
                        </div>
                        <div class="col-md-12">
                            <div class="security-dashboard-card">
                                <div class="card-header">
                                    <h6 class="card-title">Current System Load</h6>
                                    <div class="card-subtitle">CPU, Memory, Disk Usage</div>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="gauge-container">
                                                <canvas id="cpuGauge"></canvas>
                                                <div class="text-center mt-2">CPU Usage</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="gauge-container">
                                                <canvas id="memoryGauge"></canvas>
                                                <div class="text-center mt-2">Memory Usage</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="gauge-container">
                                                <canvas id="diskGauge"></canvas>
                                                <div class="text-center mt-2">Disk Usage</div>
                                            </div>
                                        </div>
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
                                    <td>{{ $event->created_at ? \Carbon\Carbon::parse($event->created_at)->diffForHumans() : 'N/A' }}</td>
                                    <td>
                                        <i class="mdi {{ $event->icon_class }} security-icon"></i>
                                        {{ ucwords(str_replace('_', ' ', $event->event_type)) }}
                                    </td>
                                    <td>
                                        @if($event->user_id)
                                            @php
                                                // Avoid querying the database inside a loop - this can cause memory issues
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
                            <p><strong>Time:</strong> {{ $event->created_at ? \Carbon\Carbon::parse($event->created_at)->format('Y-m-d H:i:s') : 'N/A' }}</p>
                            <p><strong>Event Type:</strong> {{ ucwords(str_replace('_', ' ', $event->event_type)) }}</p>
                            <p><strong>IP Address:</strong> {{ $event->ip_address }}</p>
                            <p><strong>User:</strong> 
                                @php
                                    // Avoid querying the database inside a loop - this can cause memory issues
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
                                <span class="badge {{ $event->getThreatLevelClass() }}">
                                    {{ $event->threat_level }}
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach

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
                        <h6 class="card-subtitle">System</h6>
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
                                <td>{{ \Carbon\Carbon::parse($error->created_at ?? now())->format('Y-m-d H:i') }}</td>
                                <td>
                                    <span class="badge {{ ($error->level ?? 'info') == 'error' ? 'bg-danger text-white' : (($error->level ?? 'info') == 'warning' ? 'bg-warning text-dark' : 'bg-info text-white') }}">
                                        {{ ucfirst($error->level ?? 'info') }}
                                    </span>
                                </td>
                                <td>{{ Str::limit($error->message ?? 'No message available', 40) }}</td>
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
                                                            <p><strong>Time:</strong> {{ \Carbon\Carbon::parse($error->created_at ?? now())->format('Y-m-d H:i:s') }}</p>
                                                            <p><strong>File:</strong> {{ $error->file ?? 'Unknown' }}</p>
                                                            <p><strong>Line:</strong> {{ $error->line ?? 'Unknown' }}</p>
                                                            <p><strong>User:</strong> {{ $error->user_name ?? 'Unknown' }}</p>
                                                            <p><strong>IP Address:</strong> {{ $error->ip_address ?? 'Unknown' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Message:</strong></p>
                                                            <div class="mb-3 bg-light border rounded p-3" style="word-break: break-word; max-height: 100px; overflow-y: auto;">
                                                                {{ $error->message ?? 'No message available' }}
                                                            </div>
                                                            
                                                            <p><strong>Stack Trace:</strong></p>
                                                            <div class="bg-light border rounded p-3" style="font-size: 0.8rem; max-height: 200px; overflow-y: auto;">
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
    initSecurityCharts();
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

function initSecurityCharts() {
    // Initialize all security monitoring charts
    initFailedLoginsChart();
    initBlockedRequestsChart();
    initSystemLoadGauges();
    
    // Set up auto-refresh for charts
    setInterval(function() {
        refreshSecurityCharts();
    }, 60000); // Refresh every minute
}

function initFailedLoginsChart() {
    var ctx = document.getElementById('failedLoginsChart');
    if (!ctx) return;
    
    // Fetch real data from the server
    $.ajax({
        url: '/admin/security/failed-logins-data',
        type: 'GET',
        success: function(response) {
            var data = response.data || {
                labels: ['Last 24h', 'Last 48h', 'Last 72h', 'Last Week'],
                values: [15, 25, 40, 65]
            };
            
            window.failedLoginsChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Failed Login Attempts',
                        data: data.values,
                        borderColor: 'rgba(255, 99, 132, 1)',
                        backgroundColor: 'rgba(255, 99, 132, 0.2)',
                        tension: 0.4,
                        fill: true
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
                                text: 'Number of Attempts'
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        },
                        legend: {
                            position: 'top'
                        }
                    }
                }
            });
        },
        error: function(xhr, status, error) {
            console.error('Error fetching failed logins data:', error);
        }
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

function initSystemLoadGauges() {
    // Initialize system load gauges with real-time data
    $.ajax({
        url: '/admin/security/system-load-data',
        type: 'GET',
        success: function(response) {
            var data = response.data || {
                cpu: 45,
                memory: 60,
                disk: 75
            };
            
            createGaugeChart('cpuGauge', 'CPU Usage', data.cpu, 'rgba(255, 99, 132, 1)');
            createGaugeChart('memoryGauge', 'Memory Usage', data.memory, 'rgba(54, 162, 235, 1)');
            createGaugeChart('diskGauge', 'Disk Usage', data.disk, 'rgba(75, 192, 192, 1)');
        },
        error: function(xhr, status, error) {
            console.error('Error fetching system load data:', error);
            // Initialize with default values if error occurs
            createGaugeChart('cpuGauge', 'CPU Usage', 0, 'rgba(255, 99, 132, 1)');
            createGaugeChart('memoryGauge', 'Memory Usage', 0, 'rgba(54, 162, 235, 1)');
            createGaugeChart('diskGauge', 'Disk Usage', 0, 'rgba(75, 192, 192, 1)');
        }
    });
}

function refreshSecurityCharts() {
    // Refresh all charts with new data
    $.ajax({
        url: '/admin/security/dashboard-data',
        type: 'GET',
        success: function(response) {
            // Update failed logins chart
            if (window.failedLoginsChart && response.failedLogins) {
                window.failedLoginsChart.data.labels = response.failedLogins.labels;
                window.failedLoginsChart.data.datasets[0].data = response.failedLogins.values;
                window.failedLoginsChart.update();
            }
            
            // Update blocked requests chart
            if (window.blockedRequestsChart && response.blockedRequests) {
                window.blockedRequestsChart.data.labels = response.blockedRequests.labels;
                window.blockedRequestsChart.data.datasets[0].data = response.blockedRequests.values;
                window.blockedRequestsChart.update();
            }
            
            // Update system load gauges
            if (response.systemLoad) {
                createGaugeChart('cpuGauge', 'CPU Usage', response.systemLoad.cpu, 'rgba(255, 99, 132, 1)');
                createGaugeChart('memoryGauge', 'Memory Usage', response.systemLoad.memory, 'rgba(54, 162, 235, 1)');
                createGaugeChart('diskGauge', 'Disk Usage', response.systemLoad.disk, 'rgba(75, 192, 192, 1)');
            }
            
            // Update security stats
            updateSecurityStats(response.securityStats);
        },
        error: function(xhr, status, error) {
            console.error('Error refreshing security dashboard:', error);
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

function createGaugeChart(canvasId, label, value, color) {
    var ctx = document.getElementById(canvasId);
    if (!ctx) return;
    
    // Destroy existing chart if it exists
    if (window[canvasId + 'Chart']) {
        window[canvasId + 'Chart'].destroy();
    }
    
    window[canvasId + 'Chart'] = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: [label, ''],
            datasets: [{
                data: [value, 100 - value],
                backgroundColor: [color, 'rgba(200, 200, 200, 0.2)'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            circumference: 180,
            rotation: -90,
            cutout: '75%',
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    enabled: false
                }
            }
        },
        plugins: [{
            id: 'gaugeText',
            afterDraw: function(chart) {
                var width = chart.width;
                var height = chart.height;
                var ctx = chart.ctx;
                
                ctx.restore();
                ctx.font = '1.5rem sans-serif';
                ctx.textBaseline = 'middle';
                ctx.textAlign = 'center';
                
                var text = Math.round(value) + '%';
                var textX = width / 2;
                var textY = height - (height / 4);
                
                ctx.fillStyle = color;
                ctx.fillText(text, textX, textY);
                ctx.save();
            }
        }]
    });
}

// Initialize tooltips and popovers
$(function () {
    $('[data-toggle="tooltip"]').tooltip();
    $('[data-toggle="popover"]').popover();
});
</script>
@endsection
