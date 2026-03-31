@extends('layouts.layout')
@section('content')

@include('partials.page-header', [
    'title'             => 'RESEARCH GROUPS',
    'searchRoute'       => 'researchgroup',
    'searchPlaceholder' => 'Search research group by name or description',
    'searchName'        => 'textsearch',
    'searchValue'       => $search ?? '',
])

@if(isset($noResults) && $noResults)
    <div class="container pb-5">
        <div class="no-results-message">
            <h3><ion-icon name="search-outline" class="me-2"></ion-icon> No Results Found</h3>
            <p>Sorry, we could not find any research groups matching "{{ $search }}".</p>
            <a href="{{ route('researchgroup') }}" class="uds-btn uds-btn-primary text-decoration-none">
                <ion-icon name="refresh-outline" class="me-1"></ion-icon> Clear Search
            </a>
        </div>
    </div>
@else
    <div class="container pb-5">
        <div class="rg-unified-grid">
            @foreach ($resg as $rg)
                <article class="rg-unified-card">
                    <a href="{{ route('researchgroupdetail', ['id' => $rg->id]) }}" class="rg-media-link" aria-label="{{ $rg->{'group_name_'.app()->getLocale()} }}">
                        <div class="rg-media-wrap">
                            <img src="{{ asset('img/'.$rg->group_image) }}" alt="{{ $rg->{'group_name_'.app()->getLocale()} }}" class="rg-media-image">
                        </div>
                    </a>

                    <div class="rg-body">
                        <h3 class="rg-title">{{ $rg->{'group_name_'.app()->getLocale()} }}</h3>
                        <p class="rg-desc">{{ safe_str_limit($rg->{'group_desc_'.app()->getLocale()}, 170) }}</p>

                        @if(!empty($rg->{'group_main_research_'.app()->getLocale()}))
                            <p class="rg-focus"><strong>Main Focus:</strong> {{ safe_str_limit($rg->{'group_main_research_'.app()->getLocale()}, 95) }}</p>
                        @endif

                        <a href="{{ route('researchgroupdetail', ['id' => $rg->id]) }}" class="uds-btn uds-btn-ghost text-decoration-none mt-auto">View Details</a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-4">
            @include('partials.pagination', ['paginator' => $resg])
        </div>
    </div>
@endif
@stop
