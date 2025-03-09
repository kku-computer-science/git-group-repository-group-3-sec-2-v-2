@extends('dashboards.users.layouts.user-dash-layout')

@section('title', 'Security Monitoring Dashboard')

@section('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    .dashboard-card {
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }
    
    .dashboard-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0, 0, 0, 0.1);
    }
    
    .metric-value {
        font-size: 2.5rem;
        font-weight: 700;
    }
    
    .metric-label {
        font-size: 0.9rem;
        color: #6c757d;
    }
    
    .threat-high {
        color: #dc3545;
    }
    
    .threat-medium {
        color: #ffc107;
    }
    
    .threat-low {
        color: #28a745;
    }
    
    .chart-container {
        height: 250px;
    }
    
    .log-entry {
        border-left: 4px solid #eee;
        padding: 10px 15px;
        margin-bottom: 10px;
        transition: all 0.2s ease;
    }
    
    .log-entry:hover {
        background-color: rgba(0, 0, 0, 0.03);
    }
    
    .log-entry.high {
        border-left-color: #dc3545;
    }
    
    .log-entry.medium {
        border-left-color: #ffc107;
    }
    
    .log-entry.low {
        border-left-color: #28a745;
    }
    
    .log-time {
        color: #6c757d;
        font-size: 0.8rem;
    }
    
    .gauge-chart {
        position: relative;
        height: 120px;
    }
    
    .dark-mode .dashboard-card {
        background-color: #2d3748;
        color: #f7fafc;
    }
    
    .dark-mode .metric-label {
        color: #cbd5e0;
    }
    
    .dark-mode .log-entry:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="mt-4">Security Monitoring Dashboard</h1>
        <div>
            <button type="button" class="btn btn-outline-secondary me-2" id="toggleTheme">
                <i class="fas fa-moon"></i> Toggle Dark Mode
            </button>
            <button type="button" class="btn btn-primary" id="refreshDashboard">
                <i class="fas fa-sync-alt"></i> Refresh Data
            </button>
        </div>
    </div>
    
    <!-- Security Overview Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card dashboard-card mb-4">
                <div class="card-header">
                    <i class="fas fa-shield-alt me-1"></i>
                    Security Overview
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">Total Security Alerts</div>
                                            <div class="metric-value">{{ number_format($securityOverview['total_security_alerts']) }}</div>
                                        </div>
                                        <div class="text-primary">
                                            <i class="fas fa-bell fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">Current Active Threats</div>
                                            <div class="metric-value threat-high">{{ number_format($securityOverview['active_threats']) }}</div>
                                        </div>
                                        <div class="text-danger">
                                            <i class="fas fa-exclamation-triangle fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">Blocked IPs</div>
                                            <div class="metric-value">{{ number_format($securityOverview['blocked_ips']) }}</div>
                                        </div>
                                        <div class="text-secondary">
                                            <i class="fas fa-ban fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">Suspicious Login Attempts</div>
                                            <div class="metric-value threat-medium">{{ number_format($securityOverview['suspicious_login_attempts']) }}</div>
                                        </div>
                                        <div class="text-warning">
                                            <i class="fas fa-user-secret fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-xl-6">
                            <div class="chart-container">
                                <canvas id="threatLevelChart"></canvas>
                            </div>
                        </div>
                        <div class="col-xl-6">
                            <div class="chart-container">
                                <canvas id="eventTypeChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- User Activity & Authentication Monitoring -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card dashboard-card mb-4">
                <div class="card-header">
                    <i class="fas fa-users me-1"></i>
                    User Activity & Authentication Monitoring
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">Successful Logins (Last 24h)</div>
                                            <div class="metric-value">{{ number_format($userActivity['successful_logins']) }}</div>
                                        </div>
                                        <div class="text-success">
                                            <i class="fas fa-sign-in-alt fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">Failed Logins</div>
                                            <div class="metric-value threat-medium">{{ number_format($userActivity['failed_logins']) }}</div>
                                        </div>
                                        <div class="text-danger">
                                            <i class="fas fa-user-lock fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">Account Lockouts</div>
                                            <div class="metric-value">{{ number_format($userActivity['account_lockouts']) }}</div>
                                        </div>
                                        <div class="text-warning">
                                            <i class="fas fa-lock fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">New User Registrations</div>
                                            <div class="metric-value">{{ number_format($userActivity['new_registrations']) }}</div>
                                        </div>
                                        <div class="text-info">
                                            <i class="fas fa-user-plus fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="chart-container">
                                <canvas id="loginActivityChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- API & Request Monitoring -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card dashboard-card mb-4">
                <div class="card-header">
                    <i class="fas fa-exchange-alt me-1"></i>
                    API & Request Monitoring
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-6 col-lg-6">
                            <h5>Top API Endpoints</h5>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Endpoint</th>
                                            <th>Request Count</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($apiRequestMetrics['top_endpoints'] as $endpoint)
                                        <tr>
                                            <td>{{ $endpoint['endpoint'] }}</td>
                                            <td>{{ number_format($endpoint['count']) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-6">
                            <div class="chart-container">
                                <canvas id="trafficByHourChart"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-4">
                        <div class="col-xl-6 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">Rate Limit Violations</div>
                                            <div class="metric-value threat-medium">{{ number_format($apiRequestMetrics['rate_limit_violations']) }}</div>
                                        </div>
                                        <div class="text-warning">
                                            <i class="fas fa-tachometer-alt fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-6 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">Blocked Requests (WAF Rules)</div>
                                            <div class="metric-value threat-high">{{ number_format($apiRequestMetrics['blocked_requests']) }}</div>
                                        </div>
                                        <div class="text-danger">
                                            <i class="fas fa-shield-alt fa-3x"></i>
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
    
    <!-- Incident & Threat Detection Logs -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card dashboard-card mb-4">
                <div class="card-header">
                    <i class="fas fa-exclamation-circle me-1"></i>
                    Incident & Threat Detection Logs
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">SQL Injection Attempts</div>
                                            <div class="metric-value threat-high">{{ number_format($threatDetectionLogs['sql_injection_attempts']) }}</div>
                                        </div>
                                        <div class="text-danger">
                                            <i class="fas fa-database fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">XSS Attempts</div>
                                            <div class="metric-value threat-high">{{ number_format($threatDetectionLogs['xss_attempts']) }}</div>
                                        </div>
                                        <div class="text-danger">
                                            <i class="fas fa-code fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">Unauthorized Access</div>
                                            <div class="metric-value threat-medium">{{ number_format($threatDetectionLogs['unauthorized_access']) }}</div>
                                        </div>
                                        <div class="text-warning">
                                            <i class="fas fa-door-closed fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">Server Error Rate</div>
                                            <div class="metric-value">{{ number_format($threatDetectionLogs['server_errors']) }}</div>
                                        </div>
                                        <div class="text-secondary">
                                            <i class="fas fa-server fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-12">
                            <h5>Latest Security Events (Real-Time Log)</h5>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Time</th>
                                            <th>Event Type</th>
                                            <th>IP Address</th>
                                            <th>Threat Level</th>
                                            <th>Details</th>
                                        </tr>
                                    </thead>
                                    <tbody id="security-events-log">
                                        @foreach($threatDetectionLogs['latest_events'] as $event)
                                        <tr class="{{ $event->threat_level }}">
                                            <td>{{ $event->created_at->format('M d, H:i:s') }}</td>
                                            <td>
                                                <i class="fas {{ str_replace('mdi-', 'fa-', ($event->icon_class ?? 'fa-exclamation-circle')) }} me-1"></i>
                                                {{ ucfirst(str_replace('_', ' ', $event->event_type)) }}
                                            </td>
                                            <td>{{ $event->ip_address }}</td>
                                            <td>
                                                <span class="badge bg-{{ $event->threat_level == 'high' ? 'danger' : ($event->threat_level == 'medium' ? 'warning' : 'success') }}">
                                                    {{ ucfirst($event->threat_level) }}
                                                </span>
                                            </td>
                                            <td>{{ $event->details }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end">
                                <a href="{{ route('admin.security.events') }}" class="btn btn-sm btn-primary">View All Events</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Performance & Uptime Monitoring -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card dashboard-card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-1"></i>
                    Performance & Uptime Monitoring
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-4 col-md-4">
                            <h5 class="text-center">CPU Usage</h5>
                            <div class="gauge-chart">
                                <canvas id="cpuGauge"></canvas>
                            </div>
                            <p class="text-center mt-2">{{ $performanceMetrics['system_load']['cpu'] }}%</p>
                        </div>
                        <div class="col-xl-4 col-md-4">
                            <h5 class="text-center">Memory Usage</h5>
                            <div class="gauge-chart">
                                <canvas id="memoryGauge"></canvas>
                            </div>
                            <p class="text-center mt-2">{{ $performanceMetrics['system_load']['memory'] }}%</p>
                        </div>
                        <div class="col-xl-4 col-md-4">
                            <h5 class="text-center">Disk Usage</h5>
                            <div class="gauge-chart">
                                <canvas id="diskGauge"></canvas>
                            </div>
                            <p class="text-center mt-2">{{ $performanceMetrics['system_load']['disk'] }}%</p>
                        </div>
                    </div>
                    
                    <div class="row mt-4">
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">Avg Response Time</div>
                                            <div class="metric-value">{{ $performanceMetrics['avg_response_time'] }} ms</div>
                                        </div>
                                        <div class="text-primary">
                                            <i class="fas fa-tachometer-alt fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">DB Query Time</div>
                                            <div class="metric-value">{{ $performanceMetrics['database_performance']['avg_query_time'] }} ms</div>
                                        </div>
                                        <div class="text-info">
                                            <i class="fas fa-database fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">Slow Queries</div>
                                            <div class="metric-value">{{ $performanceMetrics['database_performance']['slow_queries'] }}</div>
                                        </div>
                                        <div class="text-warning">
                                            <i class="fas fa-exclamation-triangle fa-3x"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-3 col-md-6">
                            <div class="card border-0 bg-light mb-4">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="metric-label">Uptime Status</div>
                                            <div class="metric-value text-success">{{ $performanceMetrics['uptime_status']['uptime_percentage'] }}%</div>
                                        </div>
                                        <div class="text-success">
                                            <i class="fas fa-check-circle fa-3x"></i>
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
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.7.1/dist/chart.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        try {
            // Theme toggling
            const toggleThemeBtn = document.getElementById('toggleTheme');
            if (toggleThemeBtn) {
                toggleThemeBtn.addEventListener('click', function() {
                    document.body.classList.toggle('dark-mode');
                    const isDarkMode = document.body.classList.contains('dark-mode');
                    localStorage.setItem('darkMode', isDarkMode);
                    
                    // Update charts for dark mode
                    updateChartsTheme(isDarkMode);
                });
            }
            
            // Check if dark mode was enabled
            if (localStorage.getItem('darkMode') === 'true') {
                document.body.classList.add('dark-mode');
                updateChartsTheme(true);
            }
            
            // Initialize the charts
            initCharts();
            
            // Set up refresh button
            const refreshBtn = document.getElementById('refreshDashboard');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', function() {
                    this.disabled = true;
                    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
                    
                    fetch('{{ route("admin.security.dashboard.realtime") }}')
                        .then(response => response.json())
                        .then(data => {
                            updateDashboardWithRealtimeData(data);
                            this.disabled = false;
                            this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Data';
                        })
                        .catch(error => {
                            console.error('Error refreshing dashboard:', error);
                            this.disabled = false;
                            this.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Data';
                            alert('Failed to refresh dashboard data: ' + error.message);
                        });
                });
            }
            
            // Auto-refresh every 60 seconds
            setInterval(function() {
                fetch('{{ route("admin.security.dashboard.realtime") }}')
                    .then(response => response.json())
                    .then(data => {
                        updateDashboardWithRealtimeData(data);
                    })
                    .catch(error => {
                        console.error('Error auto-refreshing dashboard:', error);
                    });
            }, 60000);
        } catch (e) {
            console.error('Error in dashboard initialization:', e);
        }
    });
    
    // Function to initialize all charts
    function initCharts() {
        try {
            console.log('Initializing charts...');
            // Check if elements exist
            const threatLevelEl = document.getElementById('threatLevelChart');
            const eventTypeEl = document.getElementById('eventTypeChart');
            const loginActivityEl = document.getElementById('loginActivityChart');
            const trafficEl = document.getElementById('trafficByHourChart');
            const cpuGaugeEl = document.getElementById('cpuGauge');
            const memoryGaugeEl = document.getElementById('memoryGauge');
            const diskGaugeEl = document.getElementById('diskGauge');
            
            if (!threatLevelEl || !eventTypeEl || !loginActivityEl || !trafficEl || 
                !cpuGaugeEl || !memoryGaugeEl || !diskGaugeEl) {
                console.warn('Some chart elements are missing from the DOM');
            }
            
            // Threat Level Distribution Chart
            if (threatLevelEl) {
                const threatLevelCtx = threatLevelEl.getContext('2d');
                window.threatLevelChart = new Chart(threatLevelCtx, {
                    type: 'pie',
                    data: {
                        labels: Object.keys({!! json_encode($securityOverview['threat_level_distribution']) !!}).map(level => level.charAt(0).toUpperCase() + level.slice(1)),
                        datasets: [{
                            data: Object.values({!! json_encode($securityOverview['threat_level_distribution']) !!}),
                            backgroundColor: ['#28a745', '#ffc107', '#dc3545'],
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'right',
                            },
                            title: {
                                display: true,
                                text: 'Threat Level Distribution'
                            }
                        }
                    }
                });
            }
            
            // Event Type Distribution Chart
            if (eventTypeEl) {
                const eventTypeCtx = eventTypeEl.getContext('2d');
                window.eventTypeChart = new Chart(eventTypeCtx, {
                    type: 'bar',
                    data: {
                        labels: Object.keys({!! json_encode($securityOverview['event_type_distribution']) !!}).map(type => 
                            type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
                        ),
                        datasets: [{
                            label: 'Event Count',
                            data: Object.values({!! json_encode($securityOverview['event_type_distribution']) !!}),
                            backgroundColor: 'rgba(54, 162, 235, 0.6)',
                            borderColor: 'rgba(54, 162, 235, 1)',
                            borderWidth: 1
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Top Security Event Types'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
            // Login Activity Chart
            if (loginActivityEl) {
                const loginActivityCtx = loginActivityEl.getContext('2d');
                window.loginActivityChart = new Chart(loginActivityCtx, {
                    type: 'line',
                    data: {
                        labels: {!! json_encode(array_column($userActivity['login_timeline'], 'hour')) !!},
                        datasets: [
                            {
                                label: 'Successful Logins',
                                data: {!! json_encode(array_column($userActivity['login_timeline'], 'successful')) !!},
                                borderColor: '#28a745',
                                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                                tension: 0.4,
                                fill: true
                            },
                            {
                                label: 'Failed Logins',
                                data: {!! json_encode(array_column($userActivity['login_timeline'], 'failed')) !!},
                                borderColor: '#dc3545',
                                backgroundColor: 'rgba(220, 53, 69, 0.1)',
                                tension: 0.4,
                                fill: true
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Login Activity (Last 24 Hours)'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
            // Traffic by Hour Chart
            if (trafficEl) {
                const trafficCtx = trafficEl.getContext('2d');
                window.trafficChart = new Chart(trafficCtx, {
                    type: 'line',
                    data: {
                        labels: Object.keys({!! json_encode($apiRequestMetrics['traffic_by_hour']) !!}).map(hour => `${hour}:00`),
                        datasets: [{
                            label: 'Traffic Volume',
                            data: Object.values({!! json_encode($apiRequestMetrics['traffic_by_hour']) !!}),
                            borderColor: '#6f42c1',
                            backgroundColor: 'rgba(111, 66, 193, 0.1)',
                            tension: 0.4,
                            fill: true
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            title: {
                                display: true,
                                text: 'Traffic by Hour (Last 24 Hours)'
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }
            
            // Create gauge charts for system metrics
            if (cpuGaugeEl) createGaugeChart('cpuGauge', {{ $performanceMetrics['system_load']['cpu'] }}, 'CPU');
            if (memoryGaugeEl) createGaugeChart('memoryGauge', {{ $performanceMetrics['system_load']['memory'] }}, 'Memory');
            if (diskGaugeEl) createGaugeChart('diskGauge', {{ $performanceMetrics['system_load']['disk'] }}, 'Disk');
            
            console.log('Charts initialization complete');
        } catch (error) {
            console.error('Error initializing charts:', error);
        }
    }
    
    // Function to create gauge charts
    function createGaugeChart(elementId, value, label) {
        try {
            const ctx = document.getElementById(elementId).getContext('2d');
            
            // Determine color based on value
            let color = '#28a745'; // green for low values
            if (value > 80) {
                color = '#dc3545'; // red for high values
            } else if (value > 60) {
                color = '#ffc107'; // yellow for medium values
            }
            
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    datasets: [{
                        data: [value, 100 - value],
                        backgroundColor: [color, '#e9ecef'],
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
                        tooltip: {
                            enabled: false
                        },
                        legend: {
                            display: false
                        }
                    }
                }
            });
        } catch (error) {
            console.error(`Error creating gauge chart for ${elementId}:`, error);
        }
    }
    
    // Function to update charts theme
    function updateChartsTheme(isDarkMode) {
        try {
            Chart.defaults.color = isDarkMode ? '#f7fafc' : '#666';
            Chart.defaults.borderColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
            
            // Redraw all charts
            if (window.threatLevelChart) window.threatLevelChart.update();
            if (window.eventTypeChart) window.eventTypeChart.update();
            if (window.loginActivityChart) window.loginActivityChart.update();
            if (window.trafficChart) window.trafficChart.update();
        } catch (error) {
            console.error('Error updating chart themes:', error);
        }
    }
    
    // Function to update dashboard with realtime data
    function updateDashboardWithRealtimeData(data) {
        try {
            console.log('Updating dashboard with real-time data:', data);
            
            // Update security overview metrics
            const metricElements = document.querySelectorAll('.metric-value');
            if (metricElements.length > 0) {
                if (data.security_overview && data.security_overview.total_security_alerts !== undefined) {
                    metricElements[0].textContent = data.security_overview.total_security_alerts.toLocaleString();
                }
                if (data.security_overview && data.security_overview.active_threats !== undefined) {
                    metricElements[1].textContent = data.security_overview.active_threats.toLocaleString();
                }
                if (data.security_overview && data.security_overview.blocked_ips !== undefined) {
                    metricElements[2].textContent = data.security_overview.blocked_ips.toLocaleString();
                }
                if (data.security_overview && data.security_overview.suspicious_login_attempts !== undefined) {
                    metricElements[3].textContent = data.security_overview.suspicious_login_attempts.toLocaleString();
                }
                
                // Update user activity metrics
                if (data.user_activity && data.user_activity.successful_logins !== undefined) {
                    metricElements[4].textContent = data.user_activity.successful_logins.toLocaleString();
                }
                if (data.user_activity && data.user_activity.failed_logins !== undefined) {
                    metricElements[5].textContent = data.user_activity.failed_logins.toLocaleString();
                }
                if (data.user_activity && data.user_activity.account_lockouts !== undefined) {
                    metricElements[6].textContent = data.user_activity.account_lockouts.toLocaleString();
                }
                if (data.user_activity && data.user_activity.new_registrations !== undefined) {
                    metricElements[7].textContent = data.user_activity.new_registrations.toLocaleString();
                }
            } else {
                console.warn('Metric elements not found');
            }
            
            // Update latest security events
            const eventsTable = document.getElementById('security-events-log');
            if (eventsTable && data.threat_detection && data.threat_detection.latest_events) {
                eventsTable.innerHTML = '';
                
                data.threat_detection.latest_events.forEach(event => {
                    const row = document.createElement('tr');
                    row.className = event.threat_level;
                    
                    const timeCell = document.createElement('td');
                    const eventDate = new Date(event.created_at);
                    timeCell.textContent = eventDate.toLocaleString('en-US', {
                        month: 'short',
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit',
                        second: '2-digit'
                    });
                    
                    const typeCell = document.createElement('td');
                    const iconClass = event.icon_class ? event.icon_class.replace('mdi-', 'fa-') : 'fa-exclamation-circle';
                    typeCell.innerHTML = `<i class="fas ${iconClass} me-1"></i>
                                      ${event.event_type.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}`;
                    
                    const ipCell = document.createElement('td');
                    ipCell.textContent = event.ip_address;
                    
                    const levelCell = document.createElement('td');
                    const badgeClass = event.threat_level === 'high' ? 'danger' : 
                                    (event.threat_level === 'medium' ? 'warning' : 'success');
                    levelCell.innerHTML = `<span class="badge bg-${badgeClass}">
                                        ${event.threat_level.charAt(0).toUpperCase() + event.threat_level.slice(1)}</span>`;
                    
                    const detailsCell = document.createElement('td');
                    detailsCell.textContent = event.details;
                    
                    row.appendChild(timeCell);
                    row.appendChild(typeCell);
                    row.appendChild(ipCell);
                    row.appendChild(levelCell);
                    row.appendChild(detailsCell);
                    
                    eventsTable.appendChild(row);
                });
            } else {
                console.warn('Events table not found or no events data available');
            }
        } catch (error) {
            console.error('Error updating dashboard with real-time data:', error);
        }
    }
</script>
@endsection 