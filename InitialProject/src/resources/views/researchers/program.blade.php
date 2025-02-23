@extends('layouts.layout')

@section('content')
<div class="container card-2 py-5">
    <p class="title">Researchers in {{ $program->program_name_en }}</p>

    <div class="d-flex justify-content-end mb-3">
        <form class="row row-cols-lg-auto g-3" method="GET"
            action="{{ route('searchresearchers', ['id' => $program->id]) }}">
            <div class="col-md-8">
                <div class="input-group">
                    <input type="text" class="form-control" name="textsearch" placeholder="Research interests">
                </div>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-outline-primary">Search</button>
            </div>
        </form>
    </div>

    <div class="row row-cols-1 row-cols-md-2 g-3">
        @foreach($users as $user)
        <a href="{{ route('detail', Crypt::encrypt($user->id)) }}" class="col text-decoration-none">
            <div class="card mb-3 shadow-sm">
                <div class="row g-0">
                    <div class="col-sm-4">
                        <img class="card-img" src="{{ $user->picture ?? asset('img/default-profile.png') }}" alt="Researcher Image">
                    </div>
                    <div class="col-sm-8 overflow-hidden" style="max-height: 220px;">
                        <div class="card-body">
                            <h5 class="card-title">
                                {{ $user->{'fname_'.app()->getLocale()} }} {{ $user->{'lname_'.app()->getLocale()} }}
                                @if($user->doctoral_degree)
                                , {{ $user->doctoral_degree }}
                                @endif
                            </h5>
                            <h5 class="card-title-2">{{ $user->position_en }}</h5>
                            <p class="card-text-1">Research interests</p>
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
        @endforeach
    </div>
</div>
@stop
