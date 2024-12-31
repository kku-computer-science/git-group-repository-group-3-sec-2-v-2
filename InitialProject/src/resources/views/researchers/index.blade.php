@extends('layouts.layout')

@section('content')
<div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="display-6 text-primary">Researchers</h1>

        <!-- Search Form -->
        <form class="row g-3 w-100" method="GET" action="{{ route('researchers.index') }}">
            <div class="col-md-10 mx-auto">
                <div class="input-group shadow-sm">
                    <input type="text" class="form-control" name="textsearch" 
                           value="{{ $search ?? '' }}" 
                           placeholder="Search by name or research interests">
                    <button type="submit" class="btn btn-primary">
                        <ion-icon name="search-outline"></ion-icon>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Accordion -->
    <div class="accordion" id="programAccordion">
        @foreach($programs as $program)
        @if($program->users->count() > 0)
        <div class="accordion-item mb-4 border-0 shadow-sm rounded">
            <h2 class="accordion-header" id="heading{{ $program->id }}">
                <button class="accordion-button bg-light {{ in_array($program->id, $expandedProgramIds) ? '' : 'collapsed' }}" 
                        type="button" 
                        data-bs-toggle="collapse" 
                        data-bs-target="#collapse{{ $program->id }}" 
                        aria-expanded="{{ in_array($program->id, $expandedProgramIds) ? 'true' : 'false' }}" 
                        aria-controls="collapse{{ $program->id }}">
                    <ion-icon name="caret-forward-outline" size="small" class="me-2"></ion-icon>
                    {{ $program->program_name_en }} ({{ $program->users->count() }})
                </button>
            </h2>
            <div id="collapse{{ $program->id }}" 
                 class="accordion-collapse collapse {{ in_array($program->id, $expandedProgramIds) ? 'show' : '' }}" 
                 aria-labelledby="heading{{ $program->id }}" 
                 data-bs-parent="#programAccordion">
                <div class="accordion-body">
                    <!-- Grid for Cards -->
                    <div class="row row-cols-1 row-cols-md-2 g-4">
                        @foreach($program->users as $user)
                        <div class="col">
                            <!-- Card -->
                            <a href="{{ route('detail', Crypt::encrypt($user->id)) }}" class="text-decoration-none">
                                <div class="card h-100 shadow-lg border-0 rounded-3">
                                    <div class="row g-0">
                                        <!-- ภาพทางซ้าย -->
                                        <div class="col-sm-4">
                                            <img class="card-image img-fluid rounded-start" 
                                                 src="{{ $user->picture ?? asset('img/default-profile.png') }}" 
                                                 alt="Researcher Image" 
                                                 style="object-fit: cover; height: 100%; max-height: 150px;">
                                        </div>
                                        <!-- ข้อมูลทางขวา -->
                                        <div class="col-sm-8">
                                            <div class="card-body">
                                                <h5 class="card-title text-primary">
                                                    {{ $user->{'fname_'.app()->getLocale()} }} {{ $user->{'lname_'.app()->getLocale()} }}
                                                    @if($user->doctoral_degree)
                                                    , {{ $user->doctoral_degree }}
                                                    @endif
                                                </h5>
                                                <h6 class="card-title-2 text-muted">{{ $user->position_en }}</h6>
                                                <p class="card-text">
                                                    <strong>Email:</strong> 
                                                    <a href="mailto:{{ $user->email }}" class="text-decoration-none">{{ $user->email }}</a>
                                                </p>
                                                <p class="card-text-1 fw-bold">Research interests</p>
                                                <div class="card-expertise">
                                                    @foreach($user->expertise->sortBy('expert_name') as $expertise)
                                                    <p class="card-text">{{ $expertise->expert_name }}</p>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endforeach
    </div>
</div>
@stop
