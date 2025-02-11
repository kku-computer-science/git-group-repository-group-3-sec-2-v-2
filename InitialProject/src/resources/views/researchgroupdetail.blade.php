@extends('layouts.layout')

<style>
/* Base styles */
.container {
    padding: 20px;
}

/* Blue stripe improvements */
.blue-stripe {
    background-color: #003e80;
    padding: 40px 20px;
    border-radius: 10px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.blue-stripe h1 {
    color: white;
    font-size: 2.5rem;
    font-weight: bold;
    margin: 0;
    text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
}

/* Content box improvements */
.research-rationale-box {
    background-color: white;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.05);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.research-rationale-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.1);
}

/* Headings */
.research-rationale-box h2 {
    color: #003e80;
    font-size: 1.8rem;
    font-weight: bold;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 2px solid #e0e0e0;
}

.research-rationale-box h3 {
    color: #333;
    font-size: 1.1rem;
    line-height: 1.6;
    margin-bottom: 0;
}

/* Member cards */
.member-card {
    padding: 20px;
    margin-bottom: 25px;
    text-align: center;
    transition: transform 0.3s ease;
}

.member-card:hover {
    transform: translateY(-5px);
}

/* Image styling */
.center-image {
    width: 180px;
    height: 180px;
    object-fit: cover;
    border-radius: 50%;
    margin-bottom: 15px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    border: 3px solid #fff;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.center-image:hover {
    transform: scale(1.05);
    box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
}

/* Person info */
.person-info {
    margin-top: 15px;
}

.person-info p {
    color: #333;
    font-size: 1.1rem;
    font-weight: 500;
    margin: 5px 0;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .blue-stripe h1 {
        font-size: 2rem;
    }

    .research-rationale-box {
        padding: 20px;
    }

    .research-rationale-box h2 {
        font-size: 1.5rem;
    }

    .center-image {
        width: 150px;
        height: 150px;
    }

    .col-md-3 {
        margin-bottom: 20px;
    }
}
</style>

@section('content')
<div class="container card-4 mt-5">
    @foreach ($resgd as $rg)
        <!-- Blue Stripe with Group Name -->
        <div class="blue-stripe">
            <h1 class="text-center">{{ $rg->{'group_name_'.app()->getLocale()} }}</h1>
        </div>

        <!-- Research Rationale -->
        <div class="research-rationale-box">
            <h2>Research Rationale</h2>
            <h3>{{ $rg->{'group_desc_'.app()->getLocale()} }}</h3>
        </div>

        <!-- Researcher Details -->
        <div class="research-rationale-box">
            <h2>Researcher Details</h2>
            <h3>{{ $rg->{'group_detail_'.app()->getLocale()} }}</h3>
        </div>

        <!-- Research Group Members -->
        <div class="research-rationale-box">
            <h2 class="text-center">Member Of Research Group</h2>
            <div class="row">
                @foreach ($rg->user as $r)
                    @if($r->hasRole('teacher'))
                        <div class="col-md-3">
                            <div class="member-card">
                                <img src="{{ $r->picture ?? asset('img/default-profile.png') }}" 
                                     alt="{{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}" 
                                     class="center-image">
                                <div class="person-info">
                                    @if(app()->getLocale() == 'en' && $r->academic_ranks_en == 'Lecturer' && $r->doctoral_degree == 'Ph.D.')
                                        <p>{{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}, Ph.D.</p>
                                    @elseif(app()->getLocale() == 'en' && $r->academic_ranks_en == 'Lecturer')
                                        <p>{{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}</p>
                                    @elseif(app()->getLocale() == 'en' && $r->doctoral_degree == 'Ph.D.')
                                        <p>{{ str_replace('Dr.', ' ', $r->{'position_'.app()->getLocale()}) }} {{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}, Ph.D.</p>
                                    @else
                                        <p>{{ $r->{'position_'.app()->getLocale()} }} {{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Students -->
        <div class="research-rationale-box">
            <h2 class="text-center">Student</h2>
            <div class="row">
                @foreach ($rg->user as $user)
                    @if($user->hasRole('student'))
                        <div class="col-md-3">
                            <div class="member-card">
                                <img src="{{ $user->picture ?? asset('img/default-profile.png') }}" 
                                     alt="{{ $user->{'fname_'.app()->getLocale()} }} {{ $user->{'lname_'.app()->getLocale()} }}" 
                                     class="center-image">
                                <div class="person-info">
                                    <p>{{ $user->{'position_'.app()->getLocale()} }} {{ $user->{'fname_'.app()->getLocale()} }} {{ $user->{'lname_'.app()->getLocale()} }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    @endforeach
</div>
@stop