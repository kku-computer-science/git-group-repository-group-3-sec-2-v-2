@extends('layouts.layout')

@section('content')
<style>
    .img-cover {
        object-fit: cover;
        object-position: center;
    }

    .card-hover:hover .card {
        transform: translateY(-5px);
        box-shadow: 0 .5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }

    .transition-all {
        transition: all 0.3s ease;
    }

    .search-form {
        max-width: 600px;
        margin: 0 auto;
    }

    .custom-accordion .accordion-button:not(.collapsed) {
        background-color: #f8f9fa;
        color: var(--bs-primary);
    }

    .custom-accordion .accordion-button:focus {
        box-shadow: none;
        border-color: rgba(0, 0, 0, 0.125);
    }

    .expertise-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .badge {
        font-weight: 500;
        padding: 0.5em 0.8em;
    }

    .readmore-content.d-none {
        display: none;
    }

    .readmore-toggle {
        cursor: pointer;
        order: 1;
    }
</style>

<div class="container-fluid py-5 px-4">
    <!-- Header Section -->
    <div class="row mb-5">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold text-primary mb-4">Our Researchers</h1>
            <form method="GET" action="{{ route('researchers.index') }}" class="search-form">
                <div class="input-group input-group-lg">
                    <input type="text" class="form-control border-2 shadow-none" name="textsearch"
                        value="{{ $search ?? '' }}" placeholder="Search researchers by name or interest..."
                        aria-label="Search researchers">
                    <button class="btn btn-primary px-4" type="submit">
                        <ion-icon name="search-outline" class="align-middle"></ion-icon>
                        <span class="ms-2">Search</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Accordion Section -->
    <div class="accordion custom-accordion" id="programAccordion">
        @foreach($programs as $program)
        @if($program->users->count() > 0)
        <div class="accordion-item border-0 rounded-4 shadow-sm mb-4 overflow-hidden">
            <h2 class="accordion-header" id="heading{{ $program->id }}">
                <button class="accordion-button fs-5 py-4 {{ in_array($program->id, $expandedProgramIds) ? '' : 'collapsed' }}"
                    type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $program->id }}"
                    aria-expanded="{{ in_array($program->id, $expandedProgramIds) ? 'true' : 'false' }}"
                    aria-controls="collapse{{ $program->id }}">
                    <ion-icon name="school-outline" class="me-3 fs-4"></ion-icon>
                    <span class="fw-semibold">{{ $program->program_name_en }}</span>
                    <span class="badge bg-primary rounded-pill ms-3">{{ $program->users->count() }}</span>
                </button>
            </h2>

            <div id="collapse{{ $program->id }}"
                class="accordion-collapse collapse {{ in_array($program->id, $expandedProgramIds) ? 'show' : '' }}"
                aria-labelledby="heading{{ $program->id }}">
                <div class="accordion-body p-4">
                    <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                        @foreach($program->users as $user)
                        <div class="col">
                            <a href="{{ route('detail', Crypt::encrypt($user->id)) }}"
                                class="text-decoration-none card-hover">
                                <div class="card h-100 border-0 shadow-sm rounded-4 transition-all">
                                    <div class="row g-0 h-100">
                                        <div class="col-md-4">
                                            <div class="h-100 position-relative">
                                                <img class="img-cover rounded-start h-100 w-100"
                                                    src="{{ $user->picture ?? asset('img/default-profile.png') }}"
                                                    alt="{{ $user->{'fname_'.app()->getLocale()} }}'s photo">
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="card-body p-4">
                                                <div class="d-flex flex-column h-100">
                                                    <h5 class="card-title text-primary mb-1">
                                                        {{ $user->{'fname_'.app()->getLocale()} }}
                                                        {{ $user->{'lname_'.app()->getLocale()} }}
                                                        @if($user->doctoral_degree)
                                                        <span class="fs-6 text-muted">, {{ $user->doctoral_degree }}</span>
                                                        @endif
                                                    </h5>
                                                    <p class="text-muted mb-3">{{ $user->position_en }}</p>

                                                    <div class="email-section mb-3">
                                                        <a href="mailto:{{ $user->email }}"
                                                            class="text-decoration-none text-primary">
                                                            <ion-icon name="mail-outline" class="align-middle me-1"></ion-icon>
                                                            {{ $user->email }}
                                                        </a>
                                                    </div>

                                                    <div class="expertise-section mt-auto">
                                                        <h6 class="fw-bold mb-2">Research Interests</h6>
                                                        <div class="expertise-tags d-flex flex-wrap align-items-start gap-1">
                                                            @php
                                                            $maxToShow = 3;
                                                            $expertiseCount = $user->expertise->count();
                                                            @endphp

                                                            @foreach($user->expertise->sortBy('expert_name')->take($maxToShow) as $expertise)
                                                            <span class="badge bg-light text-primary">
                                                                {{ $expertise->expert_name }}
                                                            </span>
                                                            @endforeach

                                                            @if($expertiseCount > $maxToShow)
                                                            <div class="readmore-content d-none">
                                                                @foreach($user->expertise->sortBy('expert_name')->slice($maxToShow) as $expertise)
                                                                <span class="badge bg-light text-primary">
                                                                    {{ $expertise->expert_name }}
                                                                </span>
                                                                @endforeach
                                                            </div>
                                                            <span class="badge bg-light text-primary readmore-toggle"
                                                                onclick="toggleReadmore(this)">
                                                                +{{ $expertiseCount - $maxToShow }} Readmore
                                                            </span>
                                                            @endif
                                                        </div>
                                                    </div>


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

<script>
    function toggleReadmore(element) {
        const readmoreContent = element.previousElementSibling;
        if (readmoreContent) {
            if (readmoreContent.classList.contains('d-none')) {
                readmoreContent.classList.remove('d-none');
                element.textContent = "Show less";
            } else {
                readmoreContent.classList.add('d-none');
                const count = readmoreContent.querySelectorAll('.badge').length;
                element.textContent = `+${count} Readmore`;
            }
            element.parentNode.appendChild(element);
        }
    }
</script>

@stop
