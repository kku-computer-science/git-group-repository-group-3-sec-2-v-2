@extends('layouts.layout')

@section('content')
<div class="container card-2 py-5">
    <p class="title">Researchers</p>

    <div class="accordion" id="programAccordion">
        @foreach($programs as $program)
            @php
                // Filter Users where is_research = 1
                $researchUsers = $program->users->where('is_research', 1);

                // Sort based on position and degree preference
                $sortedUsers = $researchUsers->sortBy([
                    function ($user) {
                        $positions = [
                            'Prof. Dr.',
                            'Assoc. Prof. Dr.',
                            'Asst. Prof. Dr.',
                            'Assoc. Prof.',
                            'Asst. Prof.',
                            'Lecturer'
                        ];
                        return array_search($user->position_en, $positions);
                    },
                    function ($user) {
                        return $user->doctoral_degree === 'Ph.D.' ? 0 : 1;
                    }
                ]);
            @endphp

            <div class="accordion-item mb-4">
                <h2 class="accordion-header" id="heading{{ $program->id }}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse{{ $program->id }}" aria-expanded="false"
                            aria-controls="collapse{{ $program->id }}">
                        <ion-icon name="caret-forward-outline" size="small" class="me-2"></ion-icon>
                        {{ $program->program_name_en }}
                        ({{ $researchUsers->count() }})
                    </button>
                </h2>

                <div id="collapse{{ $program->id }}" class="accordion-collapse collapse"
                     aria-labelledby="heading{{ $program->id }}" data-bs-parent="#programAccordion">
                    <div class="accordion-body">
                        <div class="d-flex justify-content-end mb-3">
                            <form class="row row-cols-lg-auto g-3" method="GET"
                                  action="{{ route('searchresearchers', ['id' => $program->id]) }}">
                                <div class="col-md-8">
                                    <div class="input-group">
                                        <input type="text" class="form-control" name="textsearch"
                                               placeholder="Research interests">
                                        <input type="hidden" name="is_research" value="1">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-outline-primary">
                                        Search
                                    </button>
                                </div>
                            </form>
                        </div>

                        @if($sortedUsers->count())
                            <div class="row row-cols-1 row-cols-md-2 g-3">
                                @foreach($sortedUsers as $user)
                                    <a href="{{ route('detail', Crypt::encrypt($user->id)) }}" class="col text-decoration-none">
                                        <div class="card mb-3 shadow-sm">
                                            <div class="row g-0">
                                                <div class="col-sm-4">
                                                    <img class="card-img"
                                                         src="{{ $user->picture ?? asset('img/default-profile.png') }}"
                                                         alt="Researcher Image">
                                                </div>
                                                <div class="col-sm-8 overflow-hidden" style="max-height: 220px;">
                                                    <div class="card-body">
                                                        <h5 class="card-title">
                                                            {{ $user->{'fname_'.app()->getLocale()} }}
                                                            {{ $user->{'lname_'.app()->getLocale()} }}
                                                            @if($user->doctoral_degree)
                                                                , {{ $user->doctoral_degree }}
                                                            @endif
                                                        </h5>
                                                        <h5 class="card-title-2">
                                                            {{ $user->position_en }}
                                                        </h5>
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
                        @else
                            <p class="text-center text-muted">No researchers found.</p>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@stop