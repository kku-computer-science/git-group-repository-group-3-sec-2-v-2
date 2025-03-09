@extends('dashboards.users.layouts.user-dash-layout')

@section('title', 'Error Logs')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .logs-container {
        padding: 20px;
        background: #f8f9fe;
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
        background: linear-gradient(87deg, #11cdef, #1171ef) !important;
        border-radius: 15px 15px 0 0;
        color: white;
    }

    .content-card .card-title {
        margin: 0;
        color: white;
        font-size: 1.25rem;
        font-weight: 600;
    }

    .content-card .card-subtitle {
        color: rgba(255, 255, 255, 0.8);
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
        color: #525f7f;
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

    .table tr:hover {
        background-color: #f8f9fa;
    }

    .badge {
        padding: 5px 10px;
        font-size: 0.75rem;
        font-weight: 600;
        border-radius: 5px;
    }

    .filter-form {
        padding: 20px;
        background: white;
        border-radius: 15px;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        margin-bottom: 30px;
        border-left: 4px solid #5e72e4;
    }

    .pagination {
        justify-content: center;
        margin-top: 20px;
    }

    .btn-primary {
        background-color: #5e72e4;
        border-color: #5e72e4;
    }

    .btn-primary:hover {
        background-color: #324cdd;
        border-color: #324cdd;
    }

    .btn-secondary {
        background-color: #8898aa;
        border-color: #8898aa;
    }

    .btn-secondary:hover {
        background-color: #6c757d;
        border-color: #6c757d;
    }

    .btn-info {
        background-color: #11cdef;
        border-color: #11cdef;
        transition: all 0.3s ease;
    }

    .btn-info:hover {
        background-color: #0da5c0;
        border-color: #0da5c0;
    }

    .select2-container .select2-selection--single {
        height: 38px !important;
        border: 1px solid #ced4da !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }

    .form-control:focus,
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #5e72e4 !important;
        box-shadow: 0 0 0 0.2rem rgba(94, 114, 228, 0.25) !important;
    }

    .page-header {
        background: linear-gradient(87deg, #5e72e4, #825ee4);
        padding: 30px;
        border-radius: 15px;
        margin-bottom: 30px;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .page-title-container {
        flex: 1;
    }

    .page-title {
        margin: 0;
        font-weight: 600;
    }

    .page-subtitle {
        opacity: 0.8;
        margin-bottom: 0;
    }

    .modal-header {
        background: linear-gradient(87deg, #5e72e4, #825ee4);
        color: white;
        border-radius: 0.3rem 0.3rem 0 0;
    }

    .modal-title {
        color: white;
    }

    .close {
        color: white;
        text-shadow: none;
        opacity: 0.8;
    }

    .close:hover {
        color: white;
        opacity: 1;
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-row {
        margin-bottom: 1rem;
    }

    .filter-buttons {
        margin-top: 1.5rem;
    }

    .badge.bg-login {
        background-color: #5e72e4 !important;
        color: white !important;
    }

    .badge.bg-error {
        background-color: #f5365c !important;
        color: white !important;
    }

    .badge.bg-warning {
        background-color: #fb6340 !important;
        color: white !important;
    }

    .badge.bg-info {
        background-color: #11cdef !important;
        color: white !important;
    }

    .badge.bg-emergency {
        background-color: #000000 !important;
        color: white !important;
    }

    .select2-container {
        width: 100% !important;
    }

    .back-button {
        margin-left: 15px;
    }

    pre.error-details {
        white-space: pre-wrap;
        word-break: break-word;
        background: #f8f9fa;
        padding: 15px;
        border-radius: 5px;
        border: 1px solid #e9ecef;
        font-size: 0.85rem;
        max-height: 300px;
        overflow-y: auto;
    }

    /* Style for JSON viewer */
    .json-explorer {
        font-family: 'Courier New', monospace;
    }
    
    .json-item strong {
        font-weight: 600;
    }
    
    /* Hover effect for the details button */
    .btn-details {
        transition: all 0.2s ease-in-out;
    }
    
    .btn-details:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
</style>
@endpush

@section('content')
<div class="logs-container">
    <div class="page-header">
        <div class="page-title-container">
            <h1 class="page-title">Error Logs</h1>
            <p class="page-subtitle">Monitor system errors in detail</p>
        </div>
        <div>
            <a href="{{ route('dashboard') }}" class="btn btn-light back-button">
                <i class="mdi mdi-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="filter-form">
        <form action="{{ route('admin.errors') }}" method="GET" id="error-filter-form">
            @csrf
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="level">Error Level</label>
                    <select name="level" id="level" class="form-control select2-basic">
                        <option value="">All Levels</option>
                        @foreach($errorLevels as $level)
                            <option value="{{ htmlspecialchars($level ?? '') }}" {{ request('level') == $level ? 'selected' : '' }}>
                                {{ htmlspecialchars(ucfirst($level ?? '')) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 form-group">
                    <label for="message">Error Message</label>
                    <input type="text" name="message" id="message" class="form-control" value="{{ htmlspecialchars(request('message') ?? '') }}" placeholder="Filter by error message">
                </div>
                <div class="col-md-4 form-group">
                    <label for="file">File</label>
                    <input type="text" name="file" id="file" class="form-control" value="{{ htmlspecialchars(request('file') ?? '') }}" placeholder="Filter by file path">
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 form-group">
                    <label for="ip_address">IP Address</label>
                    <input type="text" name="ip_address" id="ip_address" class="form-control" value="{{ htmlspecialchars(request('ip_address') ?? '') }}" placeholder="Filter by IP address">
                </div>
                <div class="col-md-3 form-group">
                    <label for="date_from">Date From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3 form-group">
                    <label for="date_to">Date To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2 form-group filter-buttons">
                    <button type="submit" class="btn btn-primary">
                        <i class="mdi mdi-filter"></i> Filter
                    </button>
                    <a href="{{ route('admin.errors') }}" class="btn btn-secondary ml-2">
                        <i class="mdi mdi-refresh"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <div class="content-card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h6 class="card-subtitle">System</h6>
                <h3 class="card-title">Error Logs</h3>
            </div>
            <div>
                <span class="badge bg-white text-primary">Total: {{ $errors->total() }}</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Action Type</th>
                        <th>Action</th>
                        <th>Description</th>
                        <th>IP Address</th>
                        <th>Time</th>
                        <th>Details</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($errors as $error)
                    <tr>
                        <td>{{ $error->id }}</td>
                        <td>{{ htmlspecialchars($error->user_name ?? 'Unknown') }}</td>
                        <td>
                            @php
                                $level = $error->level ?? 'info';
                                $badgeClass = 'bg-info';
                                
                                switch(strtolower($level)) {
                                    case 'error':
                                        $badgeClass = 'bg-error';
                                        break;
                                    case 'warning':
                                        $badgeClass = 'bg-warning';
                                        break;
                                    case 'emergency':
                                        $badgeClass = 'bg-emergency';
                                        break;
                                    case 'info':
                                    default:
                                        $badgeClass = 'bg-info';
                                        break;
                                }
                            @endphp
                            <span class="badge {{ $badgeClass }}">{{ htmlspecialchars(ucfirst($level ?? '')) }}</span>
                        </td>
                        <td>{{ Str::limit(htmlspecialchars($error->file ?? ''), 30) }}</td>
                        <td>{{ Str::limit(htmlspecialchars($error->message ?? ''), 50) }}</td>
                        <td>{{ htmlspecialchars($error->ip_address ?? 'Unknown') }}</td>
                        <td>{{ isset($error->created_at) ? \Carbon\Carbon::parse($error->created_at)->format('Y-m-d H:i:s') : 'N/A' }}</td>
                        <td>
                            <button class="btn btn-sm btn-info btn-details" data-toggle="modal" data-target="#errorModal{{ $error->id }}" title="View Details">
                                <i class="mdi mdi-information-outline mr-1"></i> Details
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-3">
            {{ $errors->withQueryString()->links() }}
        </div>
    </div>

    @foreach($errors as $error)
    <div class="modal fade" id="errorModal{{ $error->id }}" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        @php
                            $level = $error->level ?? 'info';
                            $badgeClass = 'bg-info';
                            
                            switch(strtolower($level)) {
                                case 'error':
                                    $badgeClass = 'bg-error';
                                    break;
                                case 'warning':
                                    $badgeClass = 'bg-warning';
                                    break;
                                case 'emergency':
                                    $badgeClass = 'bg-emergency';
                                    break;
                                case 'info':
                                default:
                                    $badgeClass = 'bg-info';
                                    break;
                            }
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ htmlspecialchars(ucfirst($level ?? '')) }}</span>
                        Error Details #{{ $error->id }}
                    </h5>
                    <button class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            @if(isset($error->context) && !empty($error->context))
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2 mb-3">Context</h6>
                                <div class="bg-light p-3 rounded">
                                    @php
                                        $contextData = $error->context;
                                        if(is_string($contextData)) {
                                            // Decode HTML entities first (convert &quot; to ")
                                            $decodedContext = html_entity_decode($contextData);
                                            // Try to decode as JSON
                                            try {
                                                $jsonData = json_decode($decodedContext, true);
                                                if (json_last_error() === JSON_ERROR_NONE) {
                                                    // It's valid JSON
                                                    $contextData = $jsonData;
                                                }
                                            } catch (\Exception $e) {
                                                // Not valid JSON, keep as string
                                            }
                                        }
                                    @endphp
                                    
                                    @if(is_array($contextData) || is_object($contextData))
                                        <div class="json-explorer">
                                            @foreach($contextData as $key => $value)
                                                <div class="json-item mb-2">
                                                    <strong class="text-primary">{{ $key }}:</strong>
                                                    @if(is_null($value))
                                                        <span class="text-muted">null</span>
                                                    @elseif(is_array($value) || is_object($value))
                                                        <pre class="mb-0 mt-1 ml-3 p-2 bg-white border rounded" style="color: #666;">{{ json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                                                    @else
                                                        <span>{{ is_bool($value) ? ($value ? 'true' : 'false') : $value }}</span>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word; color: #666;">{{ $contextData }}</pre>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            @if(isset($error->message) && !empty($error->message))
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2 mb-3">Error Message</h6>
                                <div class="alert alert-danger mb-0">
                                    @php
                                        $message = htmlspecialchars_decode($error->message ?? '');
                                        // Check if the message contains file path, highlight it
                                        $message = preg_replace('/(View: )([^)]+)/', '$1<span class="text-monospace font-weight-bold">$2</span>', $message);
                                        $message = preg_replace('/(Class ")([^"]+)(" not found)/', '$1<span class="text-danger font-weight-bold">$2</span>$3', $message);
                                    @endphp
                                    {!! $message !!}
                                </div>
                            </div>
                            @endif

                            <div class="row">
                                <div class="col-md-6">
                                    @if(isset($error->file) && !empty($error->file))
                                    <div class="mb-3">
                                        <label class="font-weight-bold">File:</label>
                                        <div class="text-monospace text-muted">{{ htmlspecialchars($error->file ?? '') }}</div>
                                    </div>
                                    @endif

                                    @if(isset($error->line) && !empty($error->line))
                                    <div class="mb-3">
                                        <label class="font-weight-bold">Line:</label>
                                        <div class="text-monospace">{{ $error->line }}</div>
                                    </div>
                                    @endif

                                    @if(isset($error->created_at))
                                    <div class="mb-3">
                                        <label class="font-weight-bold">Time:</label>
                                        <div>{{ \Carbon\Carbon::parse($error->created_at)->format('Y-m-d H:i:s') }}</div>
                                    </div>
                                    @endif
                                </div>

                                <div class="col-md-6">
                                    @if(isset($error->url) && !empty($error->url))
                                    <div class="mb-3">
                                        <label class="font-weight-bold">URL:</label>
                                        <div class="text-break">{{ htmlspecialchars($error->url ?? '') }}</div>
                                    </div>
                                    @endif

                                    @if(isset($error->method) && !empty($error->method))
                                    <div class="mb-3">
                                        <label class="font-weight-bold">Method:</label>
                                        <div><code>{{ htmlspecialchars($error->method ?? '') }}</code></div>
                                    </div>
                                    @endif

                                    @if(isset($error->ip_address) && !empty($error->ip_address))
                                    <div class="mb-3">
                                        <label class="font-weight-bold">IP Address:</label>
                                        <div><code>{{ htmlspecialchars($error->ip_address ?? '') }}</code></div>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            @if(isset($error->stack_trace) && !empty($error->stack_trace))
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2 mb-3">Stack Trace</h6>
                                <div class="bg-light p-3 rounded" style="max-height: 300px; overflow-y: auto;">
                                    <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word; color: #666;">{{ htmlspecialchars($error->stack_trace ?? '') }}</pre>
                                </div>
                            </div>
                            @endif

                            @if(isset($error->user_agent) && !empty($error->user_agent))
                            <div class="mb-4">
                                <h6 class="border-bottom pb-2 mb-3">User Agent</h6>
                                <div class="bg-light p-3 rounded">
                                    <pre class="mb-0" style="white-space: pre-wrap; word-break: break-word; color: #666;">{{ htmlspecialchars($error->user_agent ?? '') }}</pre>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2 for dropdowns
        $('.select2-basic').select2({
            width: '100%',
            dropdownParent: $('.filter-form')
        });
    });
</script>
@endpush
