@extends('layouts.layout')

<style>
    .container-fluid {
        padding-right: 0 !important;
        padding-left: 0 !important;
        max-width: 100vw;
        overflow-x: hidden;
    }

    .content-wrapper {
        padding: 0 !important;
        margin: 0 !important;
        width: 100vw !important;
        max-width: 100% !important;
    }

    .blue-stripe {
        background-color: #1075BB;
        padding: 60px 20px;
        margin-bottom: 25px;
        text-align: center;
        color: #fff;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .blue-stripe h1 {
        color: #fff;
        font-size: 2.4rem;
        font-weight: 600;
        margin: 0;
        line-height: 1.2;
    }

    .research-rationale-box {
        background-color: white;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid #eaeaea;
    }

    .research-rationale-box h2 {
        color: #003e80;
        font-size: 1.6rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eaeaea;
    }

    .research-rationale-box h3 {
        color: #003E80;
    }

    .research-rationale-box h4 {
        color: #414141;
        font-size: 1rem;
        line-height: 1.4;
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

    .member-card {
        width: 200px;
        margin: 0 auto;
    }

    .center-image {
        width: 100%;
        height: auto;
        object-fit: contain;
        border: 1px solid #eaeaea;
    }

    .profile-link {
        display: inline-block;
        text-decoration: none;
    }

    .person-info {
        text-align: center;
        margin-top: 12px;
    }

    .person-info p {
        color: #333;
        font-size: 1.1rem;
        font-weight: 500;
        margin: 5px 0;
    }

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
@foreach ($resgd as $rg)
<!-- Blue Stripe (Group Name) -->
<div class="blue-stripe">
    <h1>{{ $rg->{'group_name_'.app()->getLocale()} }}</h1>
</div>

<div class="container-fluid px-4">

    <!-- Research Rationale -->
    <div class="research-rationale-box">
        <h2>Research Rationale</h2>
        <h4>{{ $rg->{'group_desc_'.app()->getLocale()} }}</h4>
    </div>

    <!-- Main Research Areas / Topics -->
    <div class="research-rationale-box">
        <h2>Main Research Areas / Topics</h2>
        <!-- เปลี่ยนเป็น group_main_research_ ตามที่แก้ใน DB/Controller -->
        <h4>{{ $rg->{'group_main_research_'.app()->getLocale()} }}</h4>
    </div>

    <!-- Researcher Details -->
    <div class="research-rationale-box">
        <h2>Researcher Details</h2>
        <h4>{{ $rg->{'group_detail_'.app()->getLocale()} }}</h4>
    </div>

    <!-- Research Group Members (Teachers) -->
    <div class="research-rationale-box">
        <h2 class="text-center">Member Of Research Group</h2>

        <!-- (1) Head LAB (role = 1) -->
        <h3 class="mt-4">Member</h3>
        <div class="row justify-content-center g-4 mb-4">
            @foreach($rg->user as $r)
            @if($r->hasRole('teacher') && isset($r->pivot) && $r->pivot->role == 1)
            <div class="col-auto">
                <div class="member-card">
                    <div class="head-lab-badge">Head LAB</div>
                    <a href="{{ route('detail', Crypt::encrypt($r->id)) }}" class="profile-link">
                        <img src="{{ $r->picture ?? asset('img/default-profile.png') }}"
                            alt="{{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}"
                            class="center-image">
                    </a>
                    <div class="person-info">
                        @if(app()->getLocale() == 'en' && $r->academic_ranks_en == 'Lecturer' && $r->doctoral_degree == 'Ph.D.')
                        <p>{{ $r->fname_en }} {{ $r->lname_en }}, Ph.D.</p>
                        @elseif(app()->getLocale() == 'en' && $r->academic_ranks_en == 'Lecturer')
                        <p>{{ $r->fname_en }} {{ $r->lname_en }}</p>
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

        <!-- (2) สมาชิกคนอื่น (role = 2) -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4">
            @foreach($rg->user as $r)
            @if($r->hasRole('teacher') && isset($r->pivot) && $r->pivot->role == 2)
            <div class="col">
                <div class="member-card">
                    <a href="{{ route('detail', Crypt::encrypt($r->id)) }}" class="profile-link">
                        <img src="{{ $r->picture ?? asset('img/default-profile.png') }}"
                            alt="{{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}"
                            class="center-image">
                    </a>
                    <div class="person-info">
                        @if(app()->getLocale() == 'en' && $r->academic_ranks_en == 'Lecturer' && $r->doctoral_degree == 'Ph.D.')
                        <p>{{ $r->fname_en }} {{ $r->lname_en }}, Ph.D.</p>
                        @elseif(app()->getLocale() == 'en' && $r->academic_ranks_en == 'Lecturer')
                        <p>{{ $r->fname_en }} {{ $r->lname_en }}</p>
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

        <!-- Postdoctoral Researcher -->
        <h3 class="mt-5">Postdoctoral Researcher</h3>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4">
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
                        <p>{{ $r->fname_en }} {{ $r->lname_en }}, Ph.D.</p>
                        @else
                        <p>{{ $r->{'position_'.app()->getLocale()} }} {{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>

        <!-- Students -->
        <h3 class="mt-5">Students</h3>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4">
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
                        <p>
                            {{ $user->{'position_'.app()->getLocale()} }}
                            {{ $user->{'fname_'.app()->getLocale()} }}
                            {{ $user->{'lname_'.app()->getLocale()} }}
                        </p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        <!-- Visiting Scholars -->
        <h3 class="mt-5">Visiting Scholars</h3>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4">
            @foreach($rg->visitingScholars as $scholar)
            <div class="col">
                <div class="member-card">
                    <a class="profile-link">
                        <img src="{{ asset('images/imag_user/' . $scholar->picture) }}" alt="{{ $scholar->author_fname }} {{ $scholar->author_lname }}" class="center-image">
                    </a>
                    <div class="person-info">
                        <p>{{ $scholar->author_fname }} {{ $scholar->author_lname }}</p>
                        <p>{{ $scholar->belong_to }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

    </div> <!-- end research-rationale-box -->

</div> <!-- end container-fluid -->
@endforeach
@stop