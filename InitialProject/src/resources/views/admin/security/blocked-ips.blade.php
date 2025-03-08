@extends('dashboards.users.layouts.user-dash-layout')
@section('title', 'Blocked IPs')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="content-card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="card-subtitle mb-2">Security</h6>
                        <h3 class="card-title">Blocked IP Addresses</h3>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-secondary btn-sm" onclick="refreshData()">
                            <i class="mdi mdi-refresh"></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    @if(empty($ipDetails))
                        <div class="alert alert-info">
                            No IP addresses are currently blocked.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>IP Address</th>
                                        <th>Blocked At</th>
                                        <th>Reason</th>
                                        <th>Threat Score</th>
                                        <th>Blocked By</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ipDetails as $details)
                                    <tr>
                                        <td>{{ $details['ip'] }}</td>
                                        <td>{{ $details['blocked_at'] }}</td>
                                        <td>{{ $details['reason'] }}</td>
                                        <td>
                                            <span class="badge badge-{{ $details['threat_score'] >= 8 ? 'danger' : ($details['threat_score'] >= 5 ? 'warning' : 'info') }}">
                                                {{ $details['threat_score'] }}
                                            </span>
                                        </td>
                                        <td>{{ ucfirst($details['blocked_by']) }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" 
                                                    data-target="#ipModal{{ str_replace('.', '-', $details['ip']) }}">
                                                <i class="mdi mdi-information-outline"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-warning" 
                                                    onclick="unblockIP('{{ $details['ip'] }}')">
                                                <i class="mdi mdi-shield-off"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- IP Details Modal -->
                                    <div class="modal fade" id="ipModal{{ str_replace('.', '-', $details['ip']) }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">IP Details: {{ $details['ip'] }}</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p><strong>IP Address:</strong> {{ $details['ip'] }}</p>
                                                            <p><strong>Blocked At:</strong> {{ $details['blocked_at'] }}</p>
                                                            <p><strong>Threat Score:</strong> {{ $details['threat_score'] }}</p>
                                                            <p><strong>Blocked By:</strong> {{ ucfirst($details['blocked_by']) }}</p>
                                                            <p><strong>Trigger Event:</strong> {{ ucwords(str_replace('_', ' ', $details['trigger_event'])) }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Block Reason:</strong></p>
                                                            <div class="bg-light p-3 rounded mb-3">
                                                                {{ $details['reason'] }}
                                                            </div>
                                                            
                                                            <p><strong>Recent Events:</strong></p>
                                                            <div class="table-responsive">
                                                                <table class="table table-sm">
                                                                    <thead>
                                                                        <tr>
                                                                            <th>Time</th>
                                                                            <th>Event</th>
                                                                            <th>Level</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($details['recent_events'] as $event)
                                                                        <tr>
                                                                            <td>{{ $event->created_at->format('Y-m-d H:i:s') }}</td>
                                                                            <td>{{ ucwords(str_replace('_', ' ', $event->event_type)) }}</td>
                                                                            <td>
                                                                                <span class="badge badge-{{ $event->threat_level === 'high' ? 'danger' : ($event->threat_level === 'medium' ? 'warning' : 'info') }}">
                                                                                    {{ ucfirst($event->threat_level) }}
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
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-warning" 
                                                            onclick="unblockIP('{{ $details['ip'] }}')">
                                                        Unblock IP
                                                    </button>
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function refreshData() {
    location.reload();
}

function unblockIP(ip) {
    if (confirm('Are you sure you want to unblock IP: ' + ip + '?')) {
        fetch('/admin/security/unblock-ip', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ ip: ip })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('IP has been unblocked successfully');
                location.reload();
            } else {
                alert('Failed to unblock IP: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while unblocking the IP');
        });
    }
}
</script>
@endsection 