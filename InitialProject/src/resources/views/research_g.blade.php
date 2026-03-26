@extends('layouts.layout')
@section('content')

{{-- Inline styles removed — all moved to style.css (.page-header, .card, etc.) --}}

{{-- Search header --}}
@include('partials.page-header', [
    'title'             => 'RESEARCH GROUP',
    'searchRoute'       => 'researchgroup',
    'searchPlaceholder' => 'Search research group by name or description',
    'searchName'        => 'textsearch',
    'searchValue'       => $search ?? '',
])

@if(isset($noResults) && $noResults)
    <div class="no-results-message">
        <h3><ion-icon name="search-outline" class="me-2"></ion-icon> No Results Found</h3>
        <p>Sorry, we couldn't find any research groups matching "{{ $search }}".</p>
        <a href="{{ route('researchgroup') }}" class="btn btn-primary">
            <ion-icon name="refresh-outline" class="me-1"></ion-icon> Clear Search
        </a>
    </div>
@else
    <div class="container pb-5">
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-4">
            @foreach ($resg as $rg)
            <div class="col research-group-item">
                <div class="card h-100 border-0 shadow-sm" style="border-radius:12px;overflow:hidden;transition:transform .3s,box-shadow .3s;">
                    <a href="{{ route('researchgroupdetail', ['id' => $rg->id]) }}" class="text-decoration-none">
                        <div class="group-image-container">
                            <img src="{{ asset('img/'.$rg->group_image) }}" alt="Group Image" class="group-image">
                            <div class="overlay">
                                <h5 class="group-name">{{ $rg->{'group_name_'.app()->getLocale()} }}</h5>
                                <div class="group-description">
                                    {{ safe_str_limit($rg->{'group_desc_'.app()->getLocale()}, 150) }}
                                </div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-4">
            @include('partials.pagination', ['paginator' => $resg])
        </div>
    </div>
@endif

<style>
    /* Card hover effect for research group cards (page-specific) */
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.15) !important;
    }

    .group-image-container {
        position: relative;
        width: 100%;
        padding-top: 66%;
        overflow: hidden;
    }

    .group-image {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .overlay {
        position: absolute;
        top: 0; left: 0;
        width: 100%; height: 100%;
        background: linear-gradient(to bottom, rgba(0,0,0,0.25), rgba(0,0,0,0.65));
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 20px;
        text-align: center;
    }

    .group-name {
        color: white;
        font-size: 1.2rem;
        font-weight: bold;
        margin-bottom: 10px;
        transition: transform 0.3s ease;
    }

    .group-description {
        color: rgba(255,255,255,0.9);
        font-size: 0.9rem;
        opacity: 0;
        transform: translateY(15px);
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .group-image-container:hover .group-image  { transform: scale(1.08); }
    .group-image-container:hover .group-name   { transform: translateY(-8px); }
    .group-image-container:hover .group-description { opacity: 1; transform: translateY(0); }
</style>

@stop
