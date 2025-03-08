@extends('dashboards.users.layouts.user-dash-layout')
@section('title', 'Security Events')

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
                                        {{ $event->user->name ?? 'Unknown' }}<br>
                                        <small class="text-muted">{{ $event->ip_address }}</small>
                                    @else
                                        {{ $event->ip_address }}
                                    @endif
                                </td>
                                <td>{{ Str::limit($event->details, 50) }}</td>
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
                        <p><strong>Event Type:</strong> {{ ucwords(str_replace('_', ' ', $event->event_type)) }}</p>
                        <p><strong>Time:</strong> {{ $event->created_at }}</p>
                        <p><strong>IP Address:</strong> {{ $event->ip_address }}</p>
                        <p><strong>User Agent:</strong> {{ $event->user_agent }}</p>
                        <p><strong>Location:</strong> {{ $event->location ?? 'Unknown' }}</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Request Details:</strong></p>
                        <div class="bg-light p-3 rounded">
                            <pre class="mb-0">{{ json_encode($event->request_details, JSON_PRETTY_PRINT) }}</pre>
                        </div>
                        @if($event->additional_data)
                        <p class="mt-3"><strong>Additional Data:</strong></p>
                        <div class="bg-light p-3 rounded">
                            <pre class="mb-0">{{ json_encode($event->additional_data, JSON_PRETTY_PRINT) }}</pre>
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
</style>
@endsection 