@extends('dashboards.users.layouts.user-dash-layout')
@section('title', 'Blocked IPs')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <div class="content-card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                    <div>
                        <h6 class="card-subtitle text-muted text-uppercase mb-1">Security</h6>
                        <h3 class="card-title mb-0 font-weight-bold">Blocked IP Addresses</h3>
                    </div>
                    <div class="btn-group">
                        <button class="btn btn-primary" data-toggle="modal" data-target="#blockIPModal">
                            <i class="mdi mdi-shield-lock mr-1"></i> Block New IP
                        </button>
                        <button class="btn btn-outline-danger" onclick="clearAllIPs()">
                            <i class="mdi mdi-delete-sweep mr-1"></i> Clear All
                        </button>
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                            <i class="mdi mdi-arrow-left mr-1"></i> Back
                        </a>
                        <button class="btn btn-outline-primary" onclick="refreshData()">
                            <i class="mdi mdi-refresh mr-1"></i> Refresh
                        </button>
                    </div>
                </div>

                <div class="card-body">
                    <!-- Search and Filter -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="input-group shadow-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white border-right-0">
                                        <i class="mdi mdi-magnify text-muted"></i>
                                    </span>
                                </div>
                                <input type="text" id="ipSearch" class="form-control border-left-0" 
                                       placeholder="Search IP address..." 
                                       pattern="^(?:[0-9]{1,3}\.){0,3}[0-9]{1,3}$">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" onclick="filterTable()">
                                        Search
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <select id="threatFilter" class="form-control shadow-sm" onchange="filterTable()">
                                <option value="">All Threat Levels</option>
                                <option value="high">High (8-10)</option>
                                <option value="medium">Medium (5-7)</option>
                                <option value="low">Low (1-4)</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <select id="blockedByFilter" class="form-control shadow-sm" onchange="filterTable()">
                                <option value="">All Sources</option>
                                <option value="system">System</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                    </div>

                    <!-- Stats Summary -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="security-stat-card bg-danger-light shadow-hover">
                                <div class="stat-icon bg-danger shadow-sm">
                                    <i class="mdi mdi-shield-lock"></i>
                                </div>
                                <div class="stat-details">
                                    <h3 class="stat-value text-danger mb-1">{{ count($ipDetails) }}</h3>
                                    <p class="stat-label mb-0">Total Blocked IPs</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="security-stat-card bg-warning-light shadow-hover">
                                <div class="stat-icon bg-warning shadow-sm">
                                    <i class="mdi mdi-alert-circle"></i>
                                </div>
                                <div class="stat-details">
                                    <h3 class="stat-value text-warning mb-1">
                                        {{ count(array_filter($ipDetails, function($ip) { return $ip['threat_score'] >= 8; })) }}
                                    </h3>
                                    <p class="stat-label mb-0">High Threat IPs</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="security-stat-card bg-info-light shadow-hover">
                                <div class="stat-icon bg-info shadow-sm">
                                    <i class="mdi mdi-account-alert"></i>
                                </div>
                                <div class="stat-details">
                                    <h3 class="stat-value text-info mb-1">
                                        {{ count(array_filter($ipDetails, function($ip) { return $ip['blocked_by'] === 'system'; })) }}
                                    </h3>
                                    <p class="stat-label mb-0">Auto-Blocked</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="security-stat-card bg-success-light shadow-hover">
                                <div class="stat-icon bg-success shadow-sm">
                                    <i class="mdi mdi-account-key"></i>
                                </div>
                                <div class="stat-details">
                                    <h3 class="stat-value text-success mb-1">
                                        {{ count(array_filter($ipDetails, function($ip) { return $ip['blocked_by'] !== 'system'; })) }}
                                    </h3>
                                    <p class="stat-label mb-0">Manually Blocked</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if(empty($ipDetails))
                        <div class="alert alert-info d-flex align-items-center">
                            <i class="mdi mdi-information-outline mr-3" style="font-size: 24px;"></i>
                            <span>No IP addresses are currently blocked.</span>
                        </div>
                    @else
                        <div class="table-responsive shadow-sm rounded">
                            <table class="table table-hover mb-0" id="blockedIPsTable">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="border-top-0">IP Address</th>
                                        <th class="border-top-0">Blocked At</th>
                                        <th class="border-top-0">Reason</th>
                                        <th class="border-top-0">Threat Score</th>
                                        <th class="border-top-0">Blocked By</th>
                                        <th class="border-top-0">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($ipDetails as $index => $details)
                                        @php
                                            $safeIP = e($details['ip']);
                                            $safeReason = e($details['reason'] ?? 'Not specified');
                                            $safeThreatScore = (int) ($details['threat_score'] ?? 5);
                                            $safeBlockedBy = e($details['blocked_by'] ?? 'system');
                                            $safeBlockedAt = e($details['blocked_at'] ?? now()->format('Y-m-d H:i:s'));
                                            $safeTriggerEvent = e($details['trigger_event'] ?? 'manual');
                                            // Ensure unique modal ID
                                            $modalId = 'ipModal-' . $index . '-' . preg_replace('/[^a-zA-Z0-9]/', '-', $safeIP);
                                        @endphp
                                        <tr data-ip="{{ $safeIP }}" 
                                            data-threat="{{ $safeThreatScore }}" 
                                            data-blocked-by="{{ $safeBlockedBy }}" 
                                            class="align-middle">
                                            <td class="font-weight-medium">{{ $safeIP }}</td>
                                            <td>{{ $safeBlockedAt }}</td>
                                            <td>{{ Str::limit($safeReason ?? 'No reason provided', 50) }}</td>
                                            <td style="width: 200px;">
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 mr-2" style="height: 6px;">
                                                        <div class="progress-bar bg-{{ $safeThreatScore >= 8 ? 'danger' : ($safeThreatScore >= 5 ? 'warning' : 'info') }}" 
                                                             role="progressbar" 
                                                             style="width: {{ $safeThreatScore * 10 }}%" 
                                                             aria-valuenow="{{ $safeThreatScore }}" 
                                                             aria-valuemin="0" 
                                                             aria-valuemax="10">
                                                        </div>
                                                    </div>
                                                    <span class="badge badge-{{ $safeThreatScore >= 8 ? 'danger' : ($safeThreatScore >= 5 ? 'warning' : 'info') }} badge-pill">
                                                        {{ $safeThreatScore }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge badge-{{ $safeBlockedBy === 'system' ? 'info' : 'primary' }} badge-pill px-3">
                                                    {{ isset($details['blocked_by_display']) ? e($details['blocked_by_display']) : ucfirst($safeBlockedBy) }}
                                            </span>
                                        </td>
                                        <td>
                                                <button type="button" class="btn btn-sm btn-outline-info mr-1" 
                                                        data-toggle="modal" 
                                                        data-target="#{{ $modalId }}">
                                                <i class="mdi mdi-information-outline"></i>
                                            </button>
                                                <button type="button" class="btn btn-sm btn-outline-warning" 
                                                        onclick="unblockIP('{{ $safeIP }}')">
                                                <i class="mdi mdi-shield-off"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Modals - Placed outside the table to avoid UI issues -->
                        @foreach($ipDetails as $index => $details)
                            @php
                                $safeIP = e($details['ip']);
                                $safeReason = e($details['reason'] ?? 'Not specified');
                                $safeThreatScore = (int) ($details['threat_score'] ?? 5);
                                $safeBlockedBy = e($details['blocked_by'] ?? 'system');
                                $safeBlockedAt = e($details['blocked_at'] ?? now()->format('Y-m-d H:i:s'));
                                $safeTriggerEvent = e($details['trigger_event'] ?? 'manual');
                                // Ensure unique modal ID
                                $modalId = 'ipModal-' . $index . '-' . preg_replace('/[^a-zA-Z0-9]/', '-', $safeIP);
                            @endphp

                                    <!-- IP Details Modal -->
                            <div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog" aria-labelledby="{{ $modalId }}-label" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                            <div class="modal-content">
                                        <div class="modal-header border-bottom-0">
                                            <h5 class="modal-title font-weight-bold" id="{{ $modalId }}-label">
                                                <i class="mdi mdi-ip-network text-primary mr-2"></i>
                                                IP Details: {{ $safeIP }}
                                            </h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body">
                                            <!-- Modal content -->
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                    <div class="card shadow-sm mb-4">
                                                        <div class="card-body">
                                                            <h6 class="card-subtitle text-muted mb-3">Basic Information</h6>
                                                            <p class="mb-2"><strong>IP Address:</strong> {{ $safeIP }}</p>
                                                            <p class="mb-2"><strong>Blocked At:</strong> {{ $safeBlockedAt }}</p>
                                                            <p class="mb-2">
                                                                <strong>Threat Score:</strong> 
                                                                <span class="badge badge-{{ $safeThreatScore >= 8 ? 'danger' : ($safeThreatScore >= 5 ? 'warning' : 'info') }} badge-pill px-3">
                                                                    {{ $safeThreatScore }}
                                                                </span>
                                                            </p>
                                                            <p class="mb-2"><strong>Blocked By:</strong> {{ isset($details['blocked_by_display']) ? e($details['blocked_by_display']) : ucfirst($safeBlockedBy) }}</p>
                                                            <p class="mb-0"><strong>Trigger Event:</strong> {{ ucwords(str_replace('_', ' ', $safeTriggerEvent)) }}</p>
                                                        </div>
                                                    </div>
                                                    
                                                    <!-- IP Geolocation -->
                                                    <div class="card shadow-sm">
                                                        <div class="card-body">
                                                            <h6 class="card-subtitle text-muted mb-3">Geolocation Information</h6>
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="mdi mdi-earth mr-2 text-primary"></i>
                                                                <strong>Country:</strong>
                                                                <span class="ml-2" id="geo-country-{{ $index }}-{{ str_replace('.', '-', $safeIP) }}">Loading...</span>
                                                            </div>
                                                            <div class="d-flex align-items-center mb-2">
                                                                <i class="mdi mdi-city mr-2 text-primary"></i>
                                                                <strong>City:</strong>
                                                                <span class="ml-2" id="geo-city-{{ $index }}-{{ str_replace('.', '-', $safeIP) }}">Loading...</span>
                                                            </div>
                                                            <div class="d-flex align-items-center">
                                                                <i class="mdi mdi-server-network mr-2 text-primary"></i>
                                                                <strong>ISP:</strong>
                                                                <span class="ml-2" id="geo-isp-{{ $index }}-{{ str_replace('.', '-', $safeIP) }}">Loading...</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                    <div class="card shadow-sm mb-4">
                                                        <div class="card-body">
                                                            <h6 class="card-subtitle text-muted mb-3">Block Reason</h6>
                                                            <div class="bg-light p-3 rounded">
                                                                {{ $safeReason }}
                                                            </div>
                                                        </div>
                                                            </div>
                                                            
                                                    <div class="card shadow-sm">
                                                        <div class="card-body">
                                                            <h6 class="card-subtitle text-muted mb-3">Recent Events</h6>
                                                            @if(!empty($details['recent_events']))
                                                            <div class="table-responsive">
                                                                    <table class="table table-sm mb-0">
                                                                        <thead class="bg-light">
                                                                        <tr>
                                                                            <th>Time</th>
                                                                            <th>Event</th>
                                                                            <th>Level</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @foreach($details['recent_events'] as $event)
                                                                                @php
                                                                                    $safeEventType = e($event->event_type ?? 'unknown');
                                                                                    $safeThreatLevel = e($event->threat_level ?? 'low');
                                                                                    $safeCreatedAt = $event->created_at ?? now();
                                                                                    if (is_string($safeCreatedAt)) {
                                                                                        $safeCreatedAt = \Carbon\Carbon::parse($safeCreatedAt);
                                                                                    }
                                                                                @endphp
                                                                                <tr>
                                                                                    <td>{{ $safeCreatedAt->format('Y-m-d H:i:s') }}</td>
                                                                                    <td>{{ ucwords(str_replace('_', ' ', $safeEventType)) }}</td>
                                                                                    <td>
                                                                                        <span class="badge badge-{{ $safeThreatLevel === 'high' ? 'danger' : ($safeThreatLevel === 'medium' ? 'warning' : 'info') }} badge-pill">
                                                                                            {{ ucfirst($safeThreatLevel) }}
                                                                                </span>
                                                                            </td>
                                                                        </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                            @else
                                                                <div class="alert alert-info mb-0">
                                                                    No recent events recorded for this IP.
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0">
                                            <button type="button" class="btn btn-warning" 
                                                    onclick="unblockIP('{{ $safeIP }}')">
                                                <i class="mdi mdi-shield-off mr-1"></i> Unblock IP
                                            </button>
                                            <button type="button" class="btn btn-light" data-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                        
                        <!-- Pagination -->
                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <div>
                                <span class="text-muted">Showing <span id="showing-entries">{{ count($ipDetails) }}</span> of {{ count($ipDetails) }} entries</span>
                            </div>
                            <div>
                                <nav aria-label="Page navigation">
                                    <ul class="pagination mb-0" id="ip-pagination">
                                        <!-- Pagination will be generated by JavaScript -->
                                    </ul>
                                </nav>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Block IP Modal -->
<div class="modal fade" id="blockIPModal" tabindex="-1" role="dialog" aria-labelledby="blockIPModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header border-bottom-0">
                <h5 class="modal-title font-weight-bold" id="blockIPModalLabel">
                    <i class="mdi mdi-shield-lock text-primary mr-2"></i>
                    Block New IP Address
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="blockIPForm" onsubmit="return false;">
                    <div class="form-group">
                        <label for="ipAddress">IP Address</label>
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white">
                                    <i class="mdi mdi-ip text-muted"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control" id="ipAddress" 
                                   pattern="^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$"
                                   placeholder="Enter IP address (e.g. 192.168.1.1)" required>
                            <div class="invalid-feedback">Please enter a valid IP address.</div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="blockReason">Reason for Blocking</label>
                        <textarea class="form-control" id="blockReason" rows="3" 
                                  maxlength="500"
                                  placeholder="Enter reason for blocking this IP"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="threatScore">Threat Score (1-10)</label>
                        <input type="range" class="custom-range" id="threatScore" 
                               min="1" max="10" value="7">
                        <div class="d-flex justify-content-between mt-2">
                            <span class="badge badge-info badge-pill px-3">Low (1)</span>
                            <span class="badge badge-warning badge-pill px-3">Medium (5)</span>
                            <span class="badge badge-danger badge-pill px-3">High (10)</span>
                        </div>
                        <div class="text-center mt-2">
                            <span class="badge badge-primary badge-pill px-4" id="threatScoreValue">7</span>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="submitBlockIP">
                    <i class="mdi mdi-shield-lock mr-1"></i> Block IP
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Updated JavaScript with security improvements
document.addEventListener('DOMContentLoaded', function() {
    // Input validation for IP address
    const ipInput = document.getElementById('ipAddress');
    if (ipInput) {
        const ipPattern = /^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/;
        
        ipInput.addEventListener('input', function() {
            if (!ipPattern.test(this.value)) {
                this.classList.add('is-invalid');
            } else {
                this.classList.remove('is-invalid');
            }
        });
    }

    // Threat score slider
    const threatScoreSlider = document.getElementById('threatScore');
    const threatScoreValue = document.getElementById('threatScoreValue');
    
    if (threatScoreSlider && threatScoreValue) {
        threatScoreSlider.addEventListener('input', function() {
            threatScoreValue.textContent = this.value;
        });
    }
    
    // Block IP button
    const submitBlockIPButton = document.getElementById('submitBlockIP');
    if (submitBlockIPButton) {
        submitBlockIPButton.addEventListener('click', blockNewIP);
    }
    
    // Initial table filter
    filterTable();
});

function validateIPAddress(ip) {
    const pattern = /^(?:[0-9]{1,3}\.){3}[0-9]{1,3}$/;
    if (!pattern.test(ip)) return false;
    
    const parts = ip.split('.');
    return parts.every(part => {
        const num = parseInt(part, 10);
        return num >= 0 && num <= 255;
    });
}

function sanitizeInput(input) {
    if (typeof input !== 'string') return '';
    return input.replace(/[<>'"]/g, '');
}

function blockNewIP() {
    console.log('Block IP function called');
    
    const ipInput = document.getElementById('ipAddress');
    const reasonInput = document.getElementById('blockReason');
    const threatScoreInput = document.getElementById('threatScore');
    
    if (!ipInput || !reasonInput || !threatScoreInput) {
        console.error('Form elements not found');
        alert('Error: Form elements not found');
        return;
    }
    
    const ip = sanitizeInput(ipInput.value);
    const reason = sanitizeInput(reasonInput.value || 'Manually blocked by administrator');
    const threatScore = parseInt(threatScoreInput.value, 10);
    
    console.log('IP:', ip);
    console.log('Reason:', reason);
    console.log('Threat Score:', threatScore);
    
    if (!validateIPAddress(ip)) {
        alert('Please enter a valid IP address');
        ipInput.focus();
        return;
    }
    
    if (threatScore < 1 || threatScore > 10) {
        alert('Threat score must be between 1 and 10');
        threatScoreInput.focus();
        return;
    }
    
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfTokenMeta) {
        console.error('CSRF token not found');
        alert('Security error: CSRF token not found');
        return;
    }
    const csrfToken = csrfTokenMeta.content;
    
    // Show loading indicator
    const submitButton = document.getElementById('submitBlockIP');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i> Processing...';
    }
    
    console.log('Sending block IP request to /admin/security/block-ip');
    
    fetch('/admin/security/block-ip', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ 
            ip: ip,
            reason: reason,
            threat_score: threatScore
        })
    })
    .then(response => {
        console.log('Block IP response status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Block IP response data:', data);
        if (data.success) {
            alert('IP has been blocked successfully');
            
            // Close modal
            try {
                // Try jQuery method first
                $('#blockIPModal').modal('hide');
            } catch (e) {
                console.log('jQuery method failed, trying vanilla JS');
                // Fallback to vanilla JS
                const modalElement = document.getElementById('blockIPModal');
                if (modalElement) {
                    const modalInstance = bootstrap.Modal.getInstance(modalElement);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }
            }
            
            // Reload page
            location.reload();
        } else {
            alert('Failed to block IP: ' + (data.message || 'Unknown error'));
            
            // Reset button
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = '<i class="mdi mdi-shield-lock mr-1"></i> Block IP';
            }
        }
    })
    .catch(error => {
        console.error('Error blocking IP:', error);
        alert('An error occurred while blocking the IP: ' + error.message);
        
        // Reset button
        if (submitButton) {
            submitButton.disabled = false;
            submitButton.innerHTML = '<i class="mdi mdi-shield-lock mr-1"></i> Block IP';
        }
    });
}

function clearAllIPs() {
    if (!confirm('Are you sure you want to unblock ALL IP addresses? This action cannot be undone.')) {
        return;
    }
    
    // Find the clear all button and show loading state
    const clearButton = document.querySelector('button[onclick="clearAllIPs()"]');
    if (clearButton) {
        clearButton.disabled = true;
        clearButton.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i> Processing...';
    }
    
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfTokenMeta) {
        console.error('CSRF token not found');
        alert('Security error: CSRF token not found');
        
        // Reset button
        if (clearButton) {
            clearButton.disabled = false;
            clearButton.innerHTML = '<i class="mdi mdi-delete-sweep mr-1"></i> Clear All';
        }
        return;
    }
    const csrfToken = csrfTokenMeta.content;
    
    console.log('Attempting to clear all blocked IPs');
    
    fetch('/admin/security/blocked-ips/clear', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        console.log('Clear all IPs response status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
    .then(data => {
        console.log('Clear all IPs response data:', data);
        if (data.success) {
            alert('All IP addresses have been unblocked successfully');
    location.reload();
        } else {
            alert('Failed to clear blocked IPs: ' + (data.message || 'Unknown error'));
            
            // Reset button
            if (clearButton) {
                clearButton.disabled = false;
                clearButton.innerHTML = '<i class="mdi mdi-delete-sweep mr-1"></i> Clear All';
            }
        }
    })
    .catch(error => {
        console.error('Error clearing IPs:', error);
        alert('An error occurred while clearing blocked IPs: ' + error.message);
        
        // Reset button
        if (clearButton) {
            clearButton.disabled = false;
            clearButton.innerHTML = '<i class="mdi mdi-delete-sweep mr-1"></i> Clear All';
        }
    });
}

function unblockIP(ip) {
    if (!ip || !validateIPAddress(ip)) {
        alert('Invalid IP address format');
        return;
    }
    
    if (!confirm('Are you sure you want to unblock IP: ' + ip + '?')) {
        return;
    }
    
    const csrfTokenMeta = document.querySelector('meta[name="csrf-token"]');
    if (!csrfTokenMeta) {
        console.error('CSRF token not found');
        alert('Security error: CSRF token not found');
        return;
    }
    const csrfToken = csrfTokenMeta.content;
    
    // Show loading on the button
    const unblockButtons = document.querySelectorAll(`button[onclick="unblockIP('${ip}')"]`);
    unblockButtons.forEach(button => {
        button.disabled = true;
        button.innerHTML = '<i class="mdi mdi-loading mdi-spin mr-1"></i> Unblocking...';
    });
    
    console.log(`Attempting to unblock IP: ${ip}`);
    
    // Use the correct URL format for the destroy method
    fetch(`/admin/security/unblock-ip/${encodeURIComponent(ip)}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        }
    })
    .then(response => {
        console.log('Unblock response status:', response.status);
        if (!response.ok) {
            throw new Error('Network response was not ok: ' + response.status);
        }
        return response.json();
    })
        .then(data => {
        console.log('Unblock response data:', data);
            if (data.success) {
                alert('IP has been unblocked successfully');
                location.reload();
            } else {
            alert('Failed to unblock IP: ' + (data.message || 'Unknown error'));
            
            // Reset buttons
            unblockButtons.forEach(button => {
                button.disabled = false;
                button.innerHTML = '<i class="mdi mdi-shield-off"></i>';
            });
            }
        })
        .catch(error => {
        console.error('Error unblocking IP:', error);
        alert('An error occurred while unblocking the IP: ' + error.message);
        
        // Reset buttons
        unblockButtons.forEach(button => {
            button.disabled = false;
            button.innerHTML = '<i class="mdi mdi-shield-off"></i>';
        });
    });
}

function filterTable() {
    const searchInput = document.getElementById('ipSearch');
    const threatFilter = document.getElementById('threatFilter');
    const blockedByFilter = document.getElementById('blockedByFilter');
    
    if (!searchInput || !threatFilter || !blockedByFilter) {
        console.error('Filter elements not found');
        return;
    }
    
    const searchValue = sanitizeInput(searchInput.value.toLowerCase());
    const threatLevel = threatFilter.value;
    const blockedBy = blockedByFilter.value;
    
    const rows = document.querySelectorAll('#blockedIPsTable tbody tr');
    let visibleCount = 0;
    
    rows.forEach(row => {
        const ip = row.getAttribute('data-ip')?.toLowerCase() || '';
        const threat = parseInt(row.getAttribute('data-threat') || '0', 10);
        const source = row.getAttribute('data-blocked-by') || '';
        
        let show = true;
        
        if (searchValue && !ip.includes(searchValue)) {
            show = false;
        }
        
        if (threatLevel) {
            if (threatLevel === 'high' && threat < 8) show = false;
            if (threatLevel === 'medium' && (threat < 5 || threat > 7)) show = false;
            if (threatLevel === 'low' && threat > 4) show = false;
        }
        
        if (blockedBy && source !== blockedBy) {
            show = false;
        }
        
        row.style.display = show ? '' : 'none';
        if (show) visibleCount++;
    });
    
    updatePagination(visibleCount);
}

function updatePagination(visibleCount) {
    const showingEntries = document.getElementById('showing-entries');
    if (showingEntries) {
        showingEntries.textContent = visibleCount || 0;
    }
}

function refreshData() {
    location.reload();
}
</script>

<style>
/* Enhanced styles */
.security-stat-card {
    display: flex;
    align-items: center;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: transform 0.2s, box-shadow 0.2s;
}

.security-stat-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 8px;
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
    flex: 1;
}

.stat-value {
    font-size: 24px;
    font-weight: 600;
}

.stat-label {
    font-size: 14px;
    color: #6c757d;
}

.shadow-hover {
    transition: box-shadow 0.2s;
}

.shadow-hover:hover {
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.content-card {
    border-radius: 8px;
    overflow: hidden;
    background: white;
    border: 1px solid rgba(0,0,0,0.08);
}

/* Color variations */
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

/* Table styles */
.table-responsive {
    border-radius: 8px;
    overflow: hidden;
}

.table th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 12px;
    letter-spacing: 0.5px;
}

.table-hover tbody tr:hover {
    background-color: rgba(0,0,0,0.02);
}

/* Button styles */
.btn {
    border-radius: 4px;
    font-weight: 500;
    transition: all 0.2s;
}

.btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 5px rgba(0,0,0,0.1);
}

/* Modal styles */
.modal-content {
    border-radius: 8px;
    overflow: hidden;
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.modal-header {
    padding: 1.25rem 1.5rem;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.25rem 1.5rem;
}

/* Input styles */
.form-control {
    border-radius: 4px;
    border: 1px solid rgba(0,0,0,0.15);
    padding: 0.5rem 0.75rem;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus {
    box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.15);
}

.input-group-text {
    border-radius: 4px 0 0 4px;
    border: 1px solid rgba(0,0,0,0.15);
}

.custom-range::-webkit-slider-thumb {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>
@endsection 