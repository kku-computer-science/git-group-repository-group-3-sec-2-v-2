@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12 mb-4">
            <h1 class="h2">Error Logs</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Error Logs</li>
                </ol>
            </nav>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Level</th>
                                    <th>Message</th>
                                    <th>File</th>
                                    <th>Line</th>
                                    <th>Time</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($errors as $error)
                                <tr>
                                    <td>
                                        <span class="badge badge-{{ ($error->level ?? 'info') == 'error' ? 'danger' : (($error->level ?? 'info') == 'warning' ? 'warning' : 'info') }}">
                                            {{ $error->level ?? 'info' }}
                                        </span>
                                    </td>
                                    <td>{{ $error->message ?? 'No message available' }}</td>
                                    <td>{{ $error->file ?? 'Unknown' }}</td>
                                    <td>{{ $error->line ?? 'N/A' }}</td>
                                    <td>{{ isset($error->created_at) && $error->created_at ? \Carbon\Carbon::parse($error->created_at)->format('Y-m-d H:i:s') : 'N/A' }}</td>
                                    <td>
                                        <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#errorModal{{ $error->id }}">
                                            View Details
                                        </button>
                                    </td>
                                </tr>

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
                                                <h6>Stack Trace:</h6>
                                                <pre class="bg-light p-3"><code>{{ $error->stack_trace ?? 'No stack trace available' }}</code></pre>
                                                
                                                @if(isset($error->context) && $error->context)
                                                <h6 class="mt-4">Context:</h6>
                                                <pre class="bg-light p-3"><code>{{ json_encode(json_decode($error->context), JSON_PRETTY_PRINT) }}</code></pre>
                                                @endif
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $errors->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    pre {
        max-height: 400px;
        overflow-y: auto;
    }
</style>
@endpush 