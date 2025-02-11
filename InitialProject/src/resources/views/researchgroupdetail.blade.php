@extends('layouts.layout')

<style>
    /* Blue stripe style */
    .blue-stripe {
        background-color: #003e80; /* Blue background */
        padding: 30px 15px; /* Increase padding (top and bottom) to make it higher */
        border-radius: 5px; /* Optional: rounded corners */
        margin-bottom: 30px; /* Space between the stripe and other content */
    }

    .blue-stripe h1 {
        color: white; /* White text color for the heading */
        font-size: 2rem; /* Adjust font size */
        font-weight: bold;
    }

    .blue-stripe .form-control {
        background-color: #fff; /* Ensure input field has a white background */
        border: 2px solid #003e80; /* Dark blue border for input field */
        border-radius: 5px;
    }

    /* White box with shadow for Research Rationale */
    .research-rationale-box {
        background-color: white; /* White background */
        border-radius: 10px; /* Rounded corners */
        padding: 20px; /* Padding inside the box */
        box-shadow: inset 0px 4px 6px rgba(0, 0, 0, 0.1); /* Inner shadow effect */
        margin-bottom: 30px; /* Space between other sections */
    }

    /* Title within the box */
    .research-rationale-box h2 {
        font-size: 1.5rem; /* Adjust font size for the title */
        font-weight: bold;
        color: #000000; /* Blue text for the title */
    }

    /* Content within the box */
    .research-rationale-box h3 {
        font-size: 1rem; /* Adjust font size for description */
        color: #333; /* Dark text color for description */
    }
</style>

@section('content')
<div class="container card-4 mt-5">
    @foreach ($resgd as $rg)
        <!-- Blue Stripe with Group Name -->
        <div class="blue-stripe">
            <h1 class="blue-stripe text-center">{{ $rg->{'group_name_'.app()->getLocale()} }}</h1>
        </div>

        <!-- Research Rationale Box with White Background and Shadow -->
        <div class="research-rationale-box">
            <h2 class="card-text-10">Research Rationale</h2>
            <h3 class="card-text">{{ $rg->{'group_desc_'.app()->getLocale()} }}</h3>
        </div>

        <div class="research-rationale-box">
            <h2 class="card-text-10">Researcher Details</h2>
            <h3 class="card-text">{{ $rg->{'group_detail_'.app()->getLocale()} }}</h3>
        </div>

        <div class="research-rationale-box">
            <h2 class="card-text-10 text-center">Member Of Research Group</h2>
            <div class="row">
                @foreach ($rg->user as $r)
                    @if($r->hasRole('teacher'))
                        <div class="col-md-3"> <!-- This makes it a 4-column layout on medium screens and larger -->
                            <div class="text-center">
                                <img src="{{ asset('img/'.$r->profile_image) }}" alt="{{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}" class="center-image img-fluid">
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

        <div class="research-rationale-box">
            <h2 class="card-text-10 text-center">Student</h2>
            <div class="row">
                @foreach ($rg->user as $user)
                    @if($user->hasRole('student'))
                        <div class="col-md-3"> <!-- 4 columns on medium and larger screens -->
                            <div class="text-center">
                                <img src="{{ asset('img/'.$user->profile_image) }}" alt="{{ $user->{'fname_'.app()->getLocale()} }} {{ $user->{'lname_'.app()->getLocale()} }}" class="center-image img-fluid">
                                <p>{{ $user->{'position_'.app()->getLocale()} }} {{ $user->{'fname_'.app()->getLocale()} }} {{ $user->{'lname_'.app()->getLocale()} }}</p>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

    @endforeach
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
<script>
    $(document).ready(function() {
        $(".moreBox").slice(0, 1).show();
        if ($(".blogBox:hidden").length != 0) {
            $("#loadMore").show();
        }
        $("#loadMore").on('click', function(e) {
            e.preventDefault();
            $(".moreBox:hidden").slice(0, 1).slideDown();
            if ($(".moreBox:hidden").length == 0) {
                $("#loadMore").fadeOut('slow');
            }
        });
    });
</script>

@stop
<!-- <div class="card-body-research">
                    <p>Research</p>
                    <table class="table">
                        @foreach($rg->user as $user)
                        
                        <thead>
                            <tr>
                                <th><b class="name">{{$user->{'position_'.app()->getLocale()} }} {{$user->{'fname_'.app()->getLocale()} }} {{$user->{'lname_'.app()->getLocale()} }}</b></th>
                            </tr>
                            @foreach($user->paper->sortByDesc('paper_yearpub') as $p)
                            <tr class="hidden">
                                <th>
                                    <b><math>{!! html_entity_decode(preg_replace('<inf>', 'sub', $p->paper_name)) !!}</math></b> (
                                    <link>@foreach($p->teacher as $teacher){{$teacher->fname_en}} {{$teacher->lname_en}},
                                    @endforeach
                                    @foreach($p->author as $author){{$author->author_fname}} {{$author->author_lname}}@if (!$loop->last),@endif
                                    @endforeach</link>), {{$p->paper_sourcetitle}}, {{$p->paper_volume}},
                                    {{ $p->paper_yearpub }}.
                                    <a href="{{$p->paper_url}} " target="_blank">[url]</a> <a href="https://doi.org/{{$p->paper_doi}}" target="_blank">[doi]</a>
                                </th>
                            </tr>
                            @endforeach
                        </thead>
                        @endforeach
                    </table>
                </div> -->