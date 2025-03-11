@extends('layouts.layout')

@section('content')
<style>
    /* Add your existing styles here */
    
    /* ข้อความแจ้งเตือนไม่พบผลลัพธ์ */
    .no-results-message {
        text-align: center;
        padding: 40px 20px;
        background-color: #f8f9fa;
        border-radius: 10px;
        margin: 30px auto;
        max-width: 800px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }
    
    .no-results-message h3 {
        color: #2C6FA8;
        margin-bottom: 15px;
    }
    
    .no-results-message p {
        color: #6c757d;
        font-size: 1.1rem;
        margin-bottom: 20px;
    }
    
    .no-results-message .btn {
        padding: 10px 25px;
        font-weight: 500;
    }
</style>

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="display-5 fw-bold">{{ $program->program_name_en }} Researchers</h1>
            <p class="lead">Explore our researchers and their expertise</p>
        </div>
        <div class="col-md-4">
            <form method="GET" action="{{ route('researchers.program', $program->id) }}" class="d-flex">
                <input type="text" class="form-control me-2" name="textsearch" value="{{ $search ?? '' }}" placeholder="Search researchers...">
                <button type="submit" class="btn btn-primary">
                    <ion-icon name="search-outline"></ion-icon>
                </button>
            </form>
        </div>
    </div>

    @if(isset($noResults) && $noResults)
    <!-- แสดงข้อความเมื่อไม่พบผลลัพธ์ -->
    <div class="no-results-message">
        <h3><ion-icon name="search-outline" class="me-2"></ion-icon> No Results Found</h3>
        <p>Sorry, we couldn't find any researchers matching "{{ $search }}" in {{ $program->program_name_en }}.</p>
        <a href="{{ route('researchers.program', $program->id) }}" class="btn btn-primary">
            <ion-icon name="refresh-outline" class="me-1"></ion-icon> Clear Search
        </a>
    </div>
    @else
    <div class="row">
        @foreach($users as $user)
        <div class="col-md-6 mb-4">
            <a href="{{ route('detail', Crypt::encrypt($user->id)) }}" class="text-decoration-none">
                <div class="card h-100 shadow-sm">
                    <div class="card-body d-flex">
                        <img src="{{ $user->picture ?? asset('img/default-profile.png') }}" alt="{{ $user->fname_en }}'s photo" class="rounded-circle me-3" style="width: 100px; height: 100px; object-fit: cover;">
                        <div>
                            <h5 class="card-title text-primary">{{ $user->{'fname_'.app()->getLocale()} }} {{ $user->{'lname_'.app()->getLocale()} }}</h5>
                            <p class="card-text text-muted">{{ $user->position_en }}</p>
                            <a href="mailto:{{ $user->email }}" class="text-primary d-block mb-2">
                                <ion-icon name="mail-outline" class="align-middle me-1"></ion-icon>
                                {{ $user->email }}
                            </a>
                            <div class="mt-2">
                                @foreach($user->expertise->take(3) as $expertise)
                                <span class="badge bg-light text-primary me-1">{{ $expertise->expert_name }}</span>
                                @endforeach
                                @if($user->expertise->count() > 3)
                                <span class="badge bg-secondary">+{{ $user->expertise->count() - 3 }} more</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endforeach
    </div>
    @endif

    <div class="mt-4">
        <a href="{{ route('researchers.index') }}" class="btn btn-outline-primary">
            <ion-icon name="arrow-back-outline" class="align-middle me-1"></ion-icon>
            Back to All Researchers
        </a>
    </div>
</div>
@endsection
