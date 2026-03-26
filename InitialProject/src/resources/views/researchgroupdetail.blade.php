@extends('layouts.layout')
@php
    use Illuminate\Support\Facades\DB;
    use App\Models\Author;
@endphp

@section('content')
@foreach ($resgd as $rg)

{{-- Blue banner with group name (shared style, page-specific content) --}}
<div class="blue-stripe">
    <h1>{{ $rg->{'group_name_'.app()->getLocale()} }}</h1>
</div>

<div class="px-3 px-md-4 pb-5">

    <div class="research-rationale-box">
        <h2>Research Rationale</h2>
        <p>{{ $rg->{'group_desc_'.app()->getLocale()} }}</p>
    </div>

    <div class="research-rationale-box">
        <h2>Main Research Areas / Topics</h2>
        <p style="white-space:pre-wrap;">{{ $rg->{'group_main_research_'.app()->getLocale()} }}</p>
    </div>

    <div class="research-rationale-box">
        <h2>Researcher Group Details</h2>
        <p>{{ $rg->{'group_detail_'.app()->getLocale()} }}</p>
    </div>

    {{-- ─── Members ─────────────────────────────────── --}}
    <div class="research-rationale-box">
        <h2 class="text-center">Member Of Research Group</h2>

        {{-- Head LAB (role = 1) --}}
        @php $headLabs = $rg->user->filter(fn($r) => $r->hasRole('teacher') && isset($r->pivot) && $r->pivot->role == 1); @endphp
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
        @php $members = $rg->user->filter(fn($r) => $r->hasRole('teacher') && isset($r->pivot) && $r->pivot->role == 2); @endphp
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
        @if($rg->user->where('pivot.role', 3)->isNotEmpty())
        <h3 class="mt-5">Postdoctoral Researcher (Internal)</h3>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-4">
            @foreach($rg->user->filter(fn($r) => isset($r->pivot) && $r->pivot->role == 3) as $r)
            <div class="col">
                @include('partials.member-card', ['member' => $r, 'isUser' => true, 'isHeadLab' => false, 'locale' => app()->getLocale()])
            </div>
            @endforeach
        </div>
        @endif

        {{-- Postdoctoral Researcher (external, role = 3 + author_id) --}}
        @php
            $postdocExternal = [];
            $processedAuthorIds = [];
            $extPostdocs = DB::table('work_of_research_groups')
                ->where('research_group_id', $rg->id)
                ->where('role', 3)
                ->whereNull('user_id')
                ->whereNotNull('author_id')
                ->get();
            foreach($extPostdocs as $ep) {
                if (in_array($ep->author_id, $processedAuthorIds)) continue;
                $author = \App\Models\Author::find($ep->author_id);
                if ($author) { $postdocExternal[] = $author; $processedAuthorIds[] = $ep->author_id; }
            }
        @endphp
        @if(count($postdocExternal) > 0)
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
        @php $uniqueStudents = $rg->user->unique('id')->filter(fn($u) => $u->hasRole('student')); @endphp
        @if($uniqueStudents->isNotEmpty())
        <h3 class="mt-5">Students</h3>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-4">
            @foreach($uniqueStudents as $user)
            <div class="col">
                @include('partials.member-card', ['member' => $user, 'isUser' => true, 'locale' => app()->getLocale()])
            </div>
            @endforeach
        </div>
        @endif

        {{-- Visiting Scholars --}}
        @php
            $visitingScholars = [];
            foreach($rg->visitingScholars as $scholar) {
                $pivotData = $rg->visitingScholars()->where('author_id', $scholar->id)->first()->pivot;
                if ($pivotData->role == 4) { $visitingScholars[] = $scholar; }
            }
        @endphp
        @if(count($visitingScholars) > 0)
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
@endforeach
@stop
