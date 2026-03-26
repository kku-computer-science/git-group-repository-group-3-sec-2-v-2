@extends('layouts.layout')

@section('content')

{{-- Blue page header with search --}}
@include('partials.page-header', [
    'title'             => trans('message.Researchers'),
    'searchRoute'       => 'researchers.index',
    'searchPlaceholder' => 'Search by research interests...',
    'searchName'        => 'textsearch',
    'searchValue'       => request('textsearch'),
])

<div class="container pb-5 researchers-container">
    <div class="accordion" id="programAccordion">
        @foreach($programs as $program)
            @php
                $researchUsers = $program->users->where('is_research', 1);
                $sortedUsers = $researchUsers->sortBy([
                    function ($user) {
                        $positions = [
                            'Prof. Dr.', 'Assoc. Prof. Dr.', 'Asst. Prof. Dr.',
                            'Assoc. Prof.', 'Asst. Prof.', 'Lecturer'
                        ];
                        $idx = array_search($user->position_en, $positions);
                        return $idx === false ? 99 : $idx;
                    },
                    function ($user) {
                        return $user->doctoral_degree === 'Ph.D.' ? 0 : 1;
                    }
                ]);
            @endphp

            <div class="accordion-item mb-3">
                <h2 class="accordion-header" id="heading{{ $program->id }}">
                    <button class="accordion-button collapsed" type="button"
                            data-bs-toggle="collapse"
                            data-bs-target="#collapse{{ $program->id }}"
                            aria-expanded="false"
                            aria-controls="collapse{{ $program->id }}">
                        <ion-icon name="caret-forward-outline" size="small" class="me-2"></ion-icon>
                        {{ $program->program_name_en }}
                        <span class="badge bg-primary ms-2">{{ $researchUsers->count() }}</span>
                    </button>
                </h2>

                <div id="collapse{{ $program->id }}"
                     class="accordion-collapse collapse"
                     aria-labelledby="heading{{ $program->id }}"
                     data-bs-parent="#programAccordion">
                    <div class="accordion-body pt-3">

                        @if($sortedUsers->count())
                            {{-- Researcher grid: 1 col mobile, 2 col tablet, 3 col large --}}
                            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-3 researchers-grid">
                                @foreach($sortedUsers as $user)
                                    <div class="col">
                                        <a href="{{ route('detail', Crypt::encrypt($user->id)) }}" class="text-decoration-none">
                                            <div class="researcher-card card h-100 shadow-sm border-0" style="border-radius: 12px;">
                                                <div class="row g-0 h-100">
                                                    {{-- Profile image column --}}
                                                    <div class="col-4 col-md-4">
                                                        <div class="card-img-wrap h-100">
                                                            <img
                                                                src="{{ $user->picture ?? asset('img/default-profile.png') }}"
                                                                alt="{{ $user->{'fname_'.app()->getLocale()} }}"
                                                                style="width:100%;height:100%;object-fit:cover;border-radius:12px 0 0 12px;">
                                                        </div>
                                                    </div>
                                                    {{-- Info column --}}
                                                    <div class="col-8 col-md-8">
                                                        <div class="card-body py-3 px-3">
                                                            <h5 class="card-title mb-1">
                                                                {{ $user->{'fname_'.app()->getLocale()} }}
                                                                {{ $user->{'lname_'.app()->getLocale()} }}
                                                                @if($user->doctoral_degree)
                                                                    , {{ $user->doctoral_degree }}
                                                                @endif
                                                            </h5>
                                                            <p class="card-position mb-0">{{ $user->position_en }}</p>
                                                            <p class="card-interest-label mt-2 mb-1">Research Interests</p>
                                                            <div class="expertise-list">
                                                                @foreach($user->expertise->sortBy('expert_name') as $expertise)
                                                                    <p>{{ $expertise->expert_name }}</p>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-center text-muted py-3">No researchers found.</p>
                        @endif

                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@stop