@extends('dashboards.users.layouts.user-dash-layout')
@section('title', 'Security Events')

@php
function getEventTypeColor($type) {
    switch ($type) {
        case 'successful_login':
            return 'success';
        case 'failed_login':
            return 'warning';
        case 'logout':
            return 'info';
        case 'sql_injection_attempt':
        case 'xss_attempt':
        case 'brute_force_attempt':
        case 'ddos_attempt':
            return 'danger';
        case 'ip_auto_blocked':
            return 'dark';
        default:
            return 'secondary';
    }
}

function getEventTypeClass($type) {
    switch ($type) {
        case 'successful_login':
            return 'success';
        case 'failed_login':
            return 'warning';
        case 'logout':
            return 'info';
        case 'sql_injection_attempt':
        case 'xss_attempt':
        case 'brute_force_attempt':
        case 'ddos_attempt':
        case 'ip_auto_blocked':
            return 'danger';
        default:
            return 'secondary';
    }
}

function getThreatLevelIcon($level) {
    switch ($level) {
        case 'high':
            return 'mdi-alert-circle';
        case 'medium':
            return 'mdi-alert';
        case 'low':
            return 'mdi-information';
        default:
            return 'mdi-help-circle';
    }
}
@endphp

@section('content')
<!-- Always include the CSRF token as both a meta tag and a hidden input for maximum compatibility -->
<meta name="csrf-token" content="{{ csrf_token() }}">
<form id="block-ip-form" method="POST" action="{{ route('admin.security.block-ip') }}" style="display:none;">
    @csrf
    <input type="hidden" name="ip" id="block-ip-input">
    <input type="hidden" name="reason" value="Blocked from security events page">
    <input type="hidden" name="threat_score" value="8">
</form>

<div class="container-fluid">
    <!-- Back Button -->
    <div class="row mb-3">
        <div class="col-12">
            <a href="{{ route('dashboard') }}" class="btn btn-secondary">
                <i class="mdi mdi-arrow-left mr-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Security</h6>
                        <h3 class="card-title">Security Events Log</h3>
                    </div>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-primary btn-sm mr-2" id="refreshDataBtn">
                            <i class="mdi mdi-refresh"></i> Refresh
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" id="exportDropdown" data-toggle="dropdown">
                                <i class="mdi mdi-export"></i> Export
                            </button>
                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('admin.security.export', ['format' => 'csv']) }}">CSV</a>
                                <a class="dropdown-item" href="{{ route('admin.security.export', ['format' => 'excel']) }}">Excel</a>
                                <a class="dropdown-item" href="{{ route('admin.security.export', ['format' => 'pdf']) }}">PDF</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card-body border-bottom">
                    <form id="filterForm" class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Event Type</label>
                                <select class="form-control" name="event_type">
                                    <option value="">All</option>
                                    @foreach($eventTypes as $type)
                                        <option value="{{ $type }}" {{ request('event_type') == $type ? 'selected' : '' }}>
                                            {{ ucwords(str_replace('_', ' ', $type)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>Threat Level</label>
                                <select class="form-control" name="threat_level">
                                    <option value="">All</option>
                                    <option value="low" {{ request('threat_level') == 'low' ? 'selected' : '' }}>Low</option>
                                    <option value="medium" {{ request('threat_level') == 'medium' ? 'selected' : '' }}>Medium</option>
                                    <option value="high" {{ request('threat_level') == 'high' ? 'selected' : '' }}>High</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Date Range</label>
                                <div class="input-group">
                                    <input type="date" class="form-control" name="date_from" value="{{ request('date_from') }}">
                                    <div class="input-group-append input-group-prepend">
                                        <span class="input-group-text">to</span>
                                    </div>
                                    <input type="date" class="form-control" name="date_to" value="{{ request('date_to') }}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>Search</label>
                                <input type="text" class="form-control" name="search" placeholder="Search in details..." value="{{ request('search') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <div>
                                    <button type="submit" class="btn btn-primary" id="filterBtn">
                                        <i class="mdi mdi-filter"></i> Filter
                                    </button>
                                    <button type="button" class="btn btn-secondary" id="resetFiltersBtn">
                                        <i class="mdi mdi-refresh"></i> Reset
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Events Table -->
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
                            @foreach($events as $event)
                            <tr>
                                <td>{{ $event->created_at->format('Y-m-d H:i:s') }}</td>
                                <td>
                                    <i class="mdi {{ $event->icon_class }} security-icon"></i>
                                    {{ ucwords(str_replace('_', ' ', $event->event_type)) }}
                                </td>
                                <td>
                                    @if($event->user_id)
                                        {{ $event->username }}<br>
                                        <small class="text-muted">{{ $event->ip_address }}</small>
                                    @else
                                        {{ $event->ip_address }}
                                    @endif
                                </td>
                                <td>{{ Str::limit($event->details ?? 'No details available', 50) }}</td>
                                <td>
                                    <span class="threat-level {{ $event->threat_level }}">
                                        {{ ucfirst($event->threat_level) }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#eventModal{{ $event->id }}">
                                        <i class="mdi mdi-information-outline"></i>
                                    </button>

                                    @if($event->threat_level === 'high')
                                    <button type="button" class="btn btn-sm btn-danger block-btn" data-ip="{{ $event->ip_address }}">
                                        <i class="mdi mdi-shield-lock"></i>
                                    </button>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($events->hasPages())
                <div class="card-footer">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="pagination-info">
                            Showing {{ $events->firstItem() }}-{{ $events->lastItem() }} 
                            of {{ $events->total() }} events
                        </div>
                        {{ $events->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Event Details Modals -->
@foreach($events as $event)
<div class="modal fade" id="eventModal{{ $event->id }}" tabindex="-1" role="dialog" aria-labelledby="eventModalLabel{{ $event->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventModalLabel{{ $event->id }}">Security Event Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="event-details-section">
                            <div class="section-header mb-3">
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="mdi {{ $event->icon_class }} text-{{ getEventTypeColor($event->event_type) }} mr-2"></i>
                                    <span>Event Information</span>
                                </h6>
                            </div>
                            
                            <div class="detail-card">
                                <div class="detail-card-body">
                                    <div class="detail-item">
                                        <span class="detail-label">Time:</span>
                                        <span class="detail-value">{{ $event->created_at->format('Y-m-d H:i:s') }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Event Type:</span>
                                        <span class="detail-value">
                                            <span class="event-type-badge {{ getEventTypeClass($event->event_type) }}">
                                                <i class="mdi {{ $event->icon_class }} mr-1"></i>
                                                {{ ucwords(str_replace('_', ' ', $event->event_type)) }}
                                            </span>
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">IP Address:</span>
                                        <span class="detail-value">
                                            <code class="ip-address">{{ $event->ip_address }}</code>
                                            @if($event->location)
                                                <span class="text-muted ml-2">({{ $event->location }})</span>
                                            @endif
                                        </span>
                                    </div>
                                    @if($event->user_id)
                                    <div class="detail-item">
                                        <span class="detail-label">User:</span>
                                        <span class="detail-value">
                                            <span class="user-badge">
                                                <i class="mdi mdi-account mr-1"></i>
                                                {{ $event->username ?? 'Unknown' }}
                                            </span>
                                        </span>
                                    </div>
                                    @endif
                                    <div class="detail-item">
                                        <span class="detail-label">Threat Level:</span>
                                        <span class="detail-value">
                                            <span class="threat-level {{ $event->threat_level }}">
                                                <i class="mdi {{ getThreatLevelIcon($event->threat_level) }} mr-1"></i>
                                                {{ ucfirst($event->threat_level) }}
                                            </span>
                                        </span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Details:</span>
                                        <span class="detail-value text-wrap">{{ $event->details }}</span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">User Agent:</span>
                                        <span class="detail-value">
                                            <small class="text-muted">{{ $event->user_agent }}</small>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="request-details-section">
                            <div class="section-header mb-3">
                                <h6 class="mb-0 d-flex align-items-center">
                                    <i class="mdi mdi-web text-primary mr-2"></i>
                                    <span>Request Details</span>
                                </h6>
                            </div>
                            
                            @if(isset($event->request_details))
                                @php
                                    $requestDetails = is_string($event->request_details) ? json_decode($event->request_details, true) : $event->request_details;
                                @endphp
                                
                                <!-- URL and Method -->
                                <div class="detail-group mb-3">
                                    <label class="text-muted mb-1">URL</label>
                                    <div class="url-display p-2 bg-light rounded">
                                        <span class="method-badge {{ strtolower($requestDetails['method'] ?? 'get') }}">
                                            {{ $requestDetails['method'] ?? 'GET' }}
                                        </span>
                                        <span class="url-text">{{ $requestDetails['url'] ?? 'N/A' }}</span>
                                    </div>
                                </div>

                                <!-- Headers -->
                                <div class="detail-group mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <label class="text-muted mb-0">Headers</label>
                                        <button class="btn btn-sm btn-link p-0" type="button" data-toggle="collapse" 
                                                data-target="#headersCollapse{{ $event->id }}">
                                            <i class="mdi mdi-chevron-down"></i>
                                        </button>
                                    </div>
                                    <div class="collapse" id="headersCollapse{{ $event->id }}">
                                        <div class="headers-container bg-light rounded p-2">
                                            @if(isset($requestDetails['headers']))
                                                @foreach($requestDetails['headers'] as $header => $values)
                                                    <div class="header-item">
                                                        <span class="header-name">{{ $header }}:</span>
                                                        <span class="header-value">{{ is_array($values) ? implode(', ', $values) : $values }}</span>
                                                    </div>
                                                @endforeach
                                            @else
                                                <p class="mb-0 text-muted">No headers available</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Detected Pattern -->
                                @if(isset($requestDetails['detected_pattern']))
                                <div class="detail-group">
                                    <label class="text-muted mb-1">Detected Pattern</label>
                                    <div class="pattern-display p-2 bg-danger-light rounded">
                                        <i class="mdi mdi-alert-circle text-danger mr-1"></i>
                                        <code>{{ $requestDetails['detected_pattern'] }}</code>
                                    </div>
                                </div>
                                @endif
                            @else
                                <p class="text-muted">No request details available</p>
                            @endif
                        </div>

                        @if($event->additional_data)
                        <div class="mt-3">
                            <div class="additional-data-section">
                                <div class="section-header mb-3">
                                    <h6 class="mb-0 d-flex align-items-center">
                                        <i class="mdi mdi-information text-info mr-2"></i>
                                        <span>Additional Information</span>
                                    </h6>
                                </div>

                                @php
                                    $additionalData = is_string($event->additional_data) ? json_decode($event->additional_data, true) : $event->additional_data;
                                @endphp

                                @if(isset($additionalData['username']) || isset($additionalData['login_time']) || isset($additionalData['login_type']))
                                <!-- Login Information -->
                                <div class="detail-card mb-3">
                                    <div class="detail-card-header">
                                        <i class="mdi mdi-login text-primary"></i>
                                        Login Details
                                    </div>
                                    <div class="detail-card-body">
                                        @if(isset($additionalData['username']))
                                            <div class="detail-item">
                                                <span class="detail-label">Username:</span>
                                                <span class="detail-value">{{ $additionalData['username'] }}</span>
                                            </div>
                                        @endif
                                        @if(isset($additionalData['login_time']))
                                            <div class="detail-item">
                                                <span class="detail-label">Login Time:</span>
                                                <span class="detail-value">{{ $additionalData['login_time'] }}</span>
                                            </div>
                                        @endif
                                        @if(isset($additionalData['login_type']))
                                            <div class="detail-item">
                                                <span class="detail-label">Login Type:</span>
                                                <span class="detail-value badge badge-info">{{ $additionalData['login_type'] }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                @if(isset($additionalData['logout_time']))
                                <!-- Logout Information -->
                                <div class="detail-card mb-3">
                                    <div class="detail-card-header">
                                        <i class="mdi mdi-logout text-warning"></i>
                                        Logout Details
                                    </div>
                                    <div class="detail-card-body">
                                        <div class="detail-item">
                                            <span class="detail-label">Logout Time:</span>
                                            <span class="detail-value">{{ $additionalData['logout_time'] }}</span>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                @if(isset($additionalData['block_reason']) || isset($additionalData['threat_score']) || isset($additionalData['trigger_event']))
                                <!-- Security Information -->
                                <div class="detail-card mb-3">
                                    <div class="detail-card-header">
                                        <i class="mdi mdi-shield-alert text-danger"></i>
                                        Security Alert
                                    </div>
                                    <div class="detail-card-body">
                                        @if(isset($additionalData['block_reason']))
                                            <div class="detail-item">
                                                <span class="detail-label">Block Reason:</span>
                                                <span class="detail-value text-danger">{{ $additionalData['block_reason'] }}</span>
                                            </div>
                                        @endif
                                        @if(isset($additionalData['threat_score']))
                                            <div class="detail-item">
                                                <span class="detail-label">Threat Score:</span>
                                                <span class="detail-value">
                                                    <div class="threat-score-badge {{ $additionalData['threat_score'] >= 8 ? 'high' : ($additionalData['threat_score'] >= 5 ? 'medium' : 'low') }}">
                                                        {{ $additionalData['threat_score'] }}/10
                                                    </div>
                                                </span>
                                            </div>
                                        @endif
                                        @if(isset($additionalData['trigger_event']))
                                            <div class="detail-item">
                                                <span class="detail-label">Trigger Event:</span>
                                                <span class="detail-value badge badge-danger">{{ str_replace('_', ' ', $additionalData['trigger_event']) }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                @endif

                                <!-- Other/Unknown Data -->
                                @php
                                    $otherData = array_diff_key($additionalData, array_flip([
                                        'username', 'login_time', 'login_type',
                                        'logout_time',
                                        'block_reason', 'threat_score', 'trigger_event'
                                    ]));
                                @endphp
                                @if(!empty($otherData))
                                <div class="detail-card">
                                    <div class="detail-card-header">
                                        <i class="mdi mdi-code-json text-secondary"></i>
                                        Other Details
                                    </div>
                                    <div class="detail-card-body">
                                        <pre class="mb-0 other-data">{{ json_encode($otherData, JSON_PRETTY_PRINT) }}</pre>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                @if($event->threat_level === 'high')
                <button type="button" class="btn btn-danger block-btn" data-ip="{{ $event->ip_address }}">
                    Block IP
                </button>
                @endif
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@section('javascript')
<script>
$(document).ready(function() {
    console.log('jQuery ready, initializing security events page');
    
    // Info button click handler
    $('.info-btn').on('click', function() {
        var eventId = $(this).data('event-id');
        $('#eventModal' + eventId).modal('show');
    });
    
    // Block IP button click handler
    $('.block-btn').on('click', function() {
        var ip = $(this).data('ip');
        blockIP(ip);
    });
    
    // Refresh button click handler
    $('#refreshDataBtn').on('click', function() {
        location.reload();
    });
    
    // Reset filters button click handler
    $('#resetFiltersBtn').on('click', function() {
        var form = $('#filterForm');
        form.find('input, select').val('');
        form.submit();
    });
});

function blockIP(ip) {
    // Confirm the action
    if (!confirm('Are you sure you want to block IP: ' + ip + '?')) {
        return;
    }
    
    console.log('Blocking IP: ' + ip);
    
    // Disable all block buttons for this IP
    var buttons = $('.block-btn[data-ip="' + ip + '"]');
    buttons.prop('disabled', true);
    buttons.html('<i class="mdi mdi-loading mdi-spin"></i>');
    
    // Set form value and submit
    $('#block-ip-input').val(ip);
    
    // Use jQuery AJAX instead of fetch API
    $.ajax({
        url: $('#block-ip-form').attr('action'),
        type: 'POST',
        data: $('#block-ip-form').serialize(),
        dataType: 'json',
        success: function(response) {
            console.log('Block IP response:', response);
            
            if (response.success) {
                alert('IP address has been blocked successfully.');
                location.reload();
            } else {
                alert('Failed to block IP: ' + (response.message || 'Unknown error'));
                // Reset buttons
                resetBlockButtons(buttons);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX error:', error);
            alert('An error occurred while blocking the IP: ' + error);
            // Reset buttons
            resetBlockButtons(buttons);
        }
    });
}

function resetBlockButtons(buttons) {
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

<style>
/* Custom Styles */
.threat-level {
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-weight: 500;
    font-size: 0.875rem;
}

.threat-level.high {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.threat-level.medium {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.threat-level.low {
    background-color: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}

.security-icon {
    font-size: 1.1rem;
    margin-right: 0.5rem;
}

/* Request Details Styling */
.request-details-section {
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.25rem;
}

.section-header {
    border-bottom: 1px solid #e9ecef;
    padding-bottom: 0.75rem;
}

.detail-group label {
    font-size: 0.875rem;
    font-weight: 500;
}

.url-display {
    font-family: monospace;
    font-size: 0.875rem;
    word-break: break-all;
}

.method-badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    font-weight: 600;
    margin-right: 0.5rem;
}

.method-badge.get { background-color: #e3f2fd; color: #1976d2; }
.method-badge.post { background-color: #e8f5e9; color: #2e7d32; }
.method-badge.put { background-color: #fff3e0; color: #f57c00; }
.method-badge.delete { background-color: #ffebee; color: #d32f2f; }

.headers-container {
    max-height: 200px;
    overflow-y: auto;
}

.header-item {
    padding: 0.25rem 0;
    font-family: monospace;
    font-size: 0.875rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
}

.header-item:last-child {
    border-bottom: none;
}

.header-name {
    color: #6c757d;
    margin-right: 0.5rem;
}

.header-value {
    word-break: break-all;
}

.pattern-display {
    font-size: 0.875rem;
}

.bg-danger-light {
    background-color: rgba(220, 53, 69, 0.1);
}

/* Scrollbar Styling */
.headers-container::-webkit-scrollbar {
    width: 6px;
}

.headers-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.headers-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.headers-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Additional Data Section Styling */
.additional-data-section {
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin-top: 1rem;
}

.detail-card {
    background-color: #fff;
    border: 1px solid rgba(0,0,0,0.1);
    border-radius: 0.5rem;
    overflow: hidden;
}

.detail-card-header {
    background-color: #f8f9fa;
    padding: 0.75rem 1rem;
    font-weight: 600;
    font-size: 0.9rem;
    border-bottom: 1px solid rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.detail-card-body {
    padding: 1rem;
}

.detail-item {
    display: flex;
    align-items: center;
    margin-bottom: 0.5rem;
}

.detail-item:last-child {
    margin-bottom: 0;
}

.detail-label {
    color: #6c757d;
    font-weight: 500;
    min-width: 120px;
    font-size: 0.875rem;
}

.detail-value {
    font-size: 0.875rem;
}

.threat-score-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.25rem 0.75rem;
    border-radius: 1rem;
    font-weight: 600;
    font-size: 0.875rem;
}

.threat-score-badge.high {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.threat-score-badge.medium {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.threat-score-badge.low {
    background-color: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}

.other-data {
    background-color: #f8f9fa;
    padding: 1rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-family: monospace;
    margin: 0;
    white-space: pre-wrap;
}

/* Event Details Section Styling */
.event-details-section {
    border: 1px solid #e9ecef;
    border-radius: 0.5rem;
    padding: 1.25rem;
    margin-bottom: 1rem;
}

.event-type-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
    line-height: 1;
    white-space: nowrap;
}

.event-type-badge.success {
    background-color: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.event-type-badge.warning {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.event-type-badge.info {
    background-color: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}

.event-type-badge.danger {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.event-type-badge.secondary {
    background-color: rgba(108, 117, 125, 0.1);
    color: #6c757d;
}

.ip-address {
    font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
    padding: 0.2rem 0.4rem;
    font-size: 0.875rem;
    background-color: #f8f9fa;
    border-radius: 0.25rem;
}

.user-badge {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    background-color: rgba(13, 110, 253, 0.1);
    color: #0d6efd;
}

/* Update existing threat level styles */
.threat-level {
    display: inline-flex;
    align-items: center;
    padding: 0.35rem 0.75rem;
    border-radius: 0.375rem;
    font-size: 0.875rem;
    font-weight: 500;
}

.threat-level.high {
    background-color: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.threat-level.medium {
    background-color: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.threat-level.low {
    background-color: rgba(23, 162, 184, 0.1);
    color: #17a2b8;
}

/* Enhance detail items */
.detail-item {
    display: flex;
    align-items: flex-start;
    margin-bottom: 0.75rem;
    line-height: 1.5;
}

.detail-label {
    flex: 0 0 100px;
    color: #6c757d;
    font-weight: 500;
    font-size: 0.875rem;
}

.detail-value {
    flex: 1;
    font-size: 0.875rem;
    padding-left: 0.5rem;
}

.text-wrap {
    white-space: normal;
    word-break: break-word;
}
</style>
@endsection 