@extends('dashboards.users.layouts.user-dash-layout')

@section('title', 'Dashboard Error')

@section('content')
<div class="container-fluid px-4">
    <div class="row">
        <div class="col-12">
            <div class="card mt-4">
                <div class="card-header bg-danger text-white">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Security Dashboard Error
                </div>
                <div class="card-body">
                    <div class="alert alert-danger">
                        <h4 class="alert-heading">Something went wrong!</h4>
                        <p>The security dashboard encountered an error while loading. Our team has been notified of this issue.</p>
                        
                        @if (session('errors') && session('errors')->has('dashboard'))
                            <p>{{ session('errors')->first('dashboard') }}</p>
                        @endif
                        
                        <hr>
                        <p class="mb-0">Please try again later or contact the system administrator if the problem persists.</p>
                    </div>
                    
                    <div class="text-center mt-4">
                        <a href="{{ route('admin.dashboard') }}" class="btn btn-primary me-2">
                            <i class="fas fa-home me-1"></i> Go to Main Dashboard
                        </a>
                        <a href="{{ route('admin.security.dashboard') }}" class="btn btn-outline-primary">
                            <i class="fas fa-sync-alt me-1"></i> Try Again
                        </a>
                    </div>
                    
                    @if (isset($error) && config('app.debug'))
                        <div class="mt-4">
                            <h5>Error Details (Debug Mode):</h5>
                            <div class="alert alert-secondary">
                                <code>{{ $error }}</code>
                            </div>
                            
                            @if (isset($trace))
                                <h6 class="mt-3">Stack Trace:</h6>
                                <div class="alert alert-secondary overflow-auto" style="max-height: 300px;">
                                    <pre><code>{{ $trace }}</code></pre>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 