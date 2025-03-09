@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Error Logs</h3>
                </div>

                <div class="card-body">
                    @include('admin.partials.filters')

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Level</th>
                                    <th>Message</th>
                                    <th>File</th>
                                    <th>Line</th>
                                    <th>User</th>
                                    <th>IP Address</th>
                                    <th>Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($errors as $error)
                                    <tr>
                                        <td>
                                            <span class="badge badge-{{ $error->level === 'error' ? 'danger' : ($error->level === 'warning' ? 'warning' : 'info') }}">
                                                {{ ucfirst($error->level ?? 'unknown') }}
                                            </span>
                                        </td>
                                        <td>{{ Str::limit($error->message ?? 'No message', 50) }}</td>
                                        <td>{{ Str::limit($error->file ?? 'Unknown file', 30) }}</td>
                                        <td>{{ $error->line ?? 'N/A' }}</td>
                                        <td>{{ $error->user_name ?? 'Unknown' }}</td>
                                        <td>{{ $error->ip_address ?? 'N/A' }}</td>
                                        <td>{{ $error->created_at ? \Carbon\Carbon::parse($error->created_at)->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#errorModal{{ $error->id }}">
                                                View Details
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center">No error logs found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($errors->count() > 0)
                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <div class="pagination-info">
                            Showing {{ $errors->firstItem() }}-{{ $errors->lastItem() }} of {{ $errors->total() }} items
                        </div>
                        <div>
                            {{ $errors->withQueryString()->links() }}
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @foreach($errors as $error)
    <div class="modal fade" id="errorModal{{ $error->id }}" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel{{ $error->id }}" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="errorModalLabel{{ $error->id }}">
                        Error Details
                        <span class="badge badge-{{ $error->level === 'error' ? 'danger' : ($error->level === 'warning' ? 'warning' : 'info') }}">
                            {{ ucfirst($error->level ?? 'unknown') }}
                        </span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <h6>Message:</h6>
                            <pre class="bg-light p-3 mb-3">{{ $error->message ?? 'No message available' }}</pre>

                            <h6>File Location:</h6>
                            <p>{{ $error->file ?? 'Unknown file' }} (Line: {{ $error->line ?? 'N/A' }})</p>

                            <h6>User:</h6>
                            <p>{{ $error->user_name ?? 'Unknown' }}</p>

                            <h6>IP Address:</h6>
                            <p>{{ $error->ip_address ?? 'N/A' }}</p>

                            <h6>Time:</h6>
                            <p>{{ $error->created_at ? \Carbon\Carbon::parse($error->created_at)->format('Y-m-d H:i:s') : 'N/A' }}</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    $('.select2-basic').select2({
        theme: 'bootstrap4',
        width: '100%'
    });
});
</script>
@endpush