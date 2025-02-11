@extends('layouts.layout')

<style>
    .container {
        padding: 20px;
    }

    /* Header section */
    .blue-stripe {
        background-color: #003e80;
        padding: 30px 20px;
        margin-bottom: 25px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .blue-stripe h1 {
        color: white;
        font-size: 2.2rem;
        font-weight: 600;
        margin: 0;
    }

    /* Content boxes */
    .research-rationale-box {
        background-color: white;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid #eaeaea;
    }

    /* Headings */
    .research-rationale-box h2 {
        color: #003e80;
        font-size: 1.6rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eaeaea;
    }

    .research-rationale-box h3 {
        color: #333;
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 0;
    }

    /* Member cards */
    .member-card {
        padding: 15px;
        margin-bottom: 20px;
        text-align: center;
        background-color: white;
        border: 1px solid #eaeaea;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    }

    .head-lab-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #003e80;
        color: white;
        padding: 4px 10px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    /* Image styling */
    .center-image {
        width: 100%;
        max-width: 200px;
        height: auto;
        margin-bottom: 15px;
        border: 1px solid #eaeaea;
        object-fit: contain;
        aspect-ratio: 3/4;
    }

    /* Profile link styles */
    .profile-link {
        display: inline-block;
        text-decoration: none;
    }

    /* Person info */
    .person-info {
        margin-top: 12px;
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
            font-size: 1.8rem;
        }

        .research-rationale-box {
            padding: 20px;
        }

        .research-rationale-box h2 {
            font-size: 1.4rem;
        }

        .center-image {
            max-width: 160px;
        }
    }
</style>

@section('content')
<div class="container-fluid px-4">
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

    <!-- Research Group Members (Teachers) -->
    <div class="research-rationale-box">
        <h2 class="text-center">Member Of Research Group</h2>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4 justify-content-center">
            @foreach($rg->user as $r)
            @if(
            $r->hasRole('teacher')
            && isset($r->pivot)
            && in_array($r->pivot->role, [1, 2])
            )
            <div class="col">
                <div class="member-card">
                    @if($r->pivot->role == 1)
                    <div class="head-lab-badge">Head LAB</div>
                    @endif
                    <a href="{{ route('detail', Crypt::encrypt($r->id)) }}" class="profile-link">
                        <img src="{{ $r->picture ?? asset('img/default-profile.png') }}"
                            alt="{{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}"
                            class="center-image">
                    </a>
                    <div class="person-info">
                        <!-- Logic แสดงตำแหน่ง/ชื่อ ตาม locale -->
                        @if(app()->getLocale() == 'en' && $r->academic_ranks_en == 'Lecturer' && $r->doctoral_degree == 'Ph.D.')
                        <p>{{ $r->{'fname_en'} }} {{ $r->{'lname_en'} }}, Ph.D.</p>
                        @elseif(app()->getLocale() == 'en' && $r->academic_ranks_en == 'Lecturer')
                        <p>{{ $r->{'fname_en'} }} {{ $r->{'lname_en'} }}</p>
                        @elseif(app()->getLocale() == 'en' && $r->doctoral_degree == 'Ph.D.')
                        <p>{{ str_replace('Dr.', ' ', $r->position_en) }} {{ $r->fname_en }} {{ $r->lname_en }}, Ph.D.</p>
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

    <!-- Postdoctoral Researchers -->
    <div class="research-rationale-box">
        <h2 class="text-center">Postdoctoral Researchers</h2>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4 justify-content-center">
            @foreach($rg->user as $r)
            @if(isset($r->pivot) && $r->pivot->role == 3)
            <div class="col">
                <div class="member-card">
                    <a href="{{ route('detail', Crypt::encrypt($r->id)) }}" class="profile-link">
                        <img src="{{ $r->picture ?? asset('img/default-profile.png') }}"
                            alt="{{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}"
                            class="center-image">
                    </a>
                    <div class="person-info">
                        @if(app()->getLocale() == 'en' && $r->doctoral_degree == 'Ph.D.')
                        <p>{{ $r->{'fname_en'} }} {{ $r->{'lname_en'} }}, Ph.D.</p>
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
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4 justify-content-center">
            @php
            $uniqueStudents = $rg->user->unique('id')->filter(function($user) {
            return $user->hasRole('student');
            });
            @endphp
            @foreach ($uniqueStudents as $user)
            <div class="col">
                <div class="member-card">
                    <a href="{{ route('detail', Crypt::encrypt($user->id)) }}" class="profile-link">
                        <img src="{{ $user->picture ?? asset('img/default-profile.png') }}"
                            alt="{{ $user->{'fname_'.app()->getLocale()} }} {{ $user->{'lname_'.app()->getLocale()} }}"
                            class="center-image">
                    </a>
                    <div class="person-info">
                        <p>{{ $user->{'position_'.app()->getLocale()} }} {{ $user->{'fname_'.app()->getLocale()} }} {{ $user->{'lname_'.app()->getLocale()} }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endforeach
</div>
@stop