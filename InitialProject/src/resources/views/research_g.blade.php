@extends('layouts.layout')
@section('content')
<div class="container card-3">
    <!-- Blue Stripe Around the Heading and Search Input Section -->
    <div class="blue-stripe">
        <h1 class="text-center">Research Group</h1>
        <div class="row mb-3">
            <div class="col-md-6 offset-md-3">
                <input type="text" id="searchInput" class="form-control" placeholder="ค้นหากลุ่มวิจัย...">
            </div>
        </div>
    </div>

    <div id="researchGroupList" class="row row-cols-1 row-cols-md-3 g-4">
        @foreach ($resg as $rg)
            <div class="col">
                <div class="card mb-4 research-group-item">
                    <div class="row g-0">
                        <div class="col-md-12">
                            <div class="card-body">
                                <!-- Image with hover effect -->
                                <a href="{{ route('researchgroupdetail', ['id' => $rg->id]) }}">
                                    <div class="group-image-container">
                                        <img src="{{ asset('img/'.$rg->group_image) }}" alt="Group Image" class="group-image">
                                        <!-- Title overlay on top of the image -->
                                        <div class="title-overlay">
                                            <h5>{{ $rg->{'group_name_'.app()->getLocale()} }}</h5>
                                        </div>
                                        <!-- Details container that will appear only on hover -->
                                        <div class="details-overlay">
                                            <h5>{{ Str::limit($rg->{'group_desc_'.app()->getLocale()}, 150) }}</h5>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#searchInput').on('keyup', function() {
            var searchValue = $(this).val().toLowerCase();

            $('.research-group-item').filter(function() {
                var groupName = $(this).find('.card-title').text().toLowerCase();
                $(this).toggle(groupName.indexOf(searchValue) > -1);
            });
        });
    });
</script>

@stop

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

    /* Image hover effect */
    .group-image {
        width: 100%; /* Make the image occupy the full width */
        height: auto; /* Keep the aspect ratio intact */
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .group-image-container {
        position: relative;
        width: 100%;
        text-align: center; /* Center content */
    }

    /* Title overlay that appears on top of the image */
    .title-overlay {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        color: white;
        font-size: 1.5rem;
        font-weight: bold;
        text-align: center;
        z-index: 2; /* Make sure it's above the image */
        opacity: 1;
        transition: opacity 0.3s ease;
    }

    /* Details overlay that will appear only on hover */
    .details-overlay {
        display: none;
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        color: white;
        padding: 15px;
        font-size: 16px;
        text-align: center;
        border-radius: 5px;
    }

    /* Show the overlay when hovering over the image */
    .group-image-container:hover .details-overlay {
        display: block; /* Show details on hover */
    }

    /* Hide title overlay on hover */
    .group-image-container:hover .title-overlay {
        opacity: 0; /* Hide title when hovered */
    }

    /* Hover effect on image */
    .group-image:hover {
        transform: scale(1.05); /* Enlarge the image */
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Add shadow on hover */
    }
</style>