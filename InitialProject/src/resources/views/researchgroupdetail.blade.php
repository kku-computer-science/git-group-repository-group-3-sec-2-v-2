@extends('layouts.layout')

@section('content')
{{-- Blue banner with group name (shared style, page-specific content) --}}
<div class="blue-stripe">
    <h1>{{ $researchGroup->{'group_name_'.app()->getLocale()} }}</h1>
</div>

<div class="px-3 px-md-4 pb-5">

    <div class="research-rationale-box">
        <h2>Research Rationale</h2>
        <p>{{ $researchGroup->{'group_desc_'.app()->getLocale()} }}</p>
    </div>

    <div class="research-rationale-box">
        <h2>Main Research Areas / Topics</h2>
        <p style="white-space:pre-wrap;">{{ $researchGroup->{'group_main_research_'.app()->getLocale()} }}</p>
    </div>

    <div class="research-rationale-box">
        <h2>Researcher Group Details</h2>
        <p>{{ $researchGroup->{'group_detail_'.app()->getLocale()} }}</p>
    </div>

    {{-- ─── Members ─────────────────────────────────── --}}
    <div class="research-rationale-box">
        <h2 class="text-center">Member Of Research Group</h2>

        {{-- Head LAB (role = 1) --}}
        @if($headLabs->isNotEmpty())
        <h3 class="mt-3">Head LAB</h3>
        <div class="row justify-content-center g-4 mb-4">
            @foreach($headLabs as $r)
            <div class="col-auto">
                @include('partials.member-card', ['member' => $r, 'isUser' => true, 'isHeadLab' => true, 'locale' => app()->getLocale()])
            </div>
            @endforeach
        </div>
        @endif

        {{-- Regular members (role = 2) --}}
        @if($members->isNotEmpty())
        <h3 class="mt-4">Member</h3>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-4">
            @foreach($members as $r)
            <div class="col">
                @include('partials.member-card', ['member' => $r, 'isUser' => true, 'isHeadLab' => false, 'locale' => app()->getLocale()])
            </div>
            @endforeach
        </div>
        @endif

        {{-- Postdoctoral Researcher (internal, role = 3) --}}
        @if($postdocInternal->isNotEmpty())
        <h3 class="mt-5">Postdoctoral Researcher (Internal)</h3>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-4">
            @foreach($postdocInternal as $r)
            <div class="col">
                @include('partials.member-card', ['member' => $r, 'isUser' => true, 'isHeadLab' => false, 'locale' => app()->getLocale()])
            </div>
            @endforeach
        </div>
        @endif

        {{-- Postdoctoral Researcher (external, role = 3 + author_id) --}}
        @if($postdocExternal->isNotEmpty())
        <h3 class="mt-5">Postdoctoral Researcher (External)</h3>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-4">
            @foreach($postdocExternal as $scholar)
            <div class="col">
                @include('partials.member-card', ['member' => $scholar, 'isUser' => false, 'locale' => app()->getLocale()])
            </div>
            @endforeach
        </div>
        @endif

        {{-- Students --}}
        @if($students->isNotEmpty())
        <h3 class="mt-5">Students</h3>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-4">
            @foreach($students as $user)
            <div class="col">
                @include('partials.member-card', ['member' => $user, 'isUser' => true, 'locale' => app()->getLocale()])
            </div>
            @endforeach
        </div>
        @endif

        {{-- Visiting Scholars --}}
        @if($visitingScholars->isNotEmpty())
        <h3 class="mt-5">Visiting Scholars</h3>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-4">
            @foreach($visitingScholars as $scholar)
            <div class="col">
                @include('partials.member-card', ['member' => $scholar, 'isUser' => false, 'locale' => app()->getLocale()])
            </div>
            @endforeach
        </div>
        @endif

    </div>{{-- end research-rationale-box --}}
</div>{{-- end px-4 --}}
@stop
