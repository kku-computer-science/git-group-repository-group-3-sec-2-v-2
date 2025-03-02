@extends('layouts.layout')
@section('content')

<style>
    .container-fluid {
        padding-right: 0 !important;
        padding-left: 0 !important;
        max-width: 100vw;
        /* ป้องกันการเกินขอบจอ */
        overflow-x: hidden;
    }

    .content-wrapper {
        padding: 0 !important;
        margin: 0 !important;
        width: 100vw !important;
        /* กว้างเต็มหน้าจอ */
        max-width: 100% !important;
        /* ป้องกันขนาดถูกบีบ */
    }

    /* ส่วน Header (สีฟ้า) */
    .header-container {
        padding: 0;
        /* เอาขอบด้านข้างออก */
        margin-bottom: 20px;
    }

    .header-row {
        height: 250px;
        background-color: #1075BB;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        box-sizing: border-box;
        margin: 0;
        /* ป้องกัน spacing เกิน */
    }

    /* ช่องค้นหาใน Header */
    .search-form {
        max-width: 600px;
        margin: 0 auto;
    }

    .input-group {
        position: relative;
        width: 100%;
    }

    .search-input {
        padding-right: 50px !important;
        /* เว้นที่ให้ปุ่มค้นหา */
        background-color: white;
        border-radius: 25px !important;
        font-size: 16px !important;
        border: none;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .search-input:focus {
        outline: none;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .search-button {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        background: transparent;
        border: none;
        cursor: pointer;
        z-index: 3;
    }

    .search-icon {
        color: rgb(0, 0, 0);
        pointer-events: none;
    }

    /* Card styles */
    .card {
        border: none;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 12px rgba(0, 0, 0, 0.15);
    }

    /* Image container and hover effects */
    .group-image-container {
        position: relative;
        width: 100%;
        padding-top: 75%;
        /* 4:3 Aspect Ratio */
        overflow: hidden;
    }

    .group-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom,
                rgba(0, 0, 0, 0.3),
                rgba(0, 0, 0, 0.7));
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 20px;
        text-align: center;
        transition: opacity 0.3s ease;
    }

    .group-name {
        color: white;
        font-size: 1.4rem;
        font-weight: bold;
        margin-bottom: 15px;
        transition: transform 0.3s ease;
    }

    .group-description {
        color: white;
        font-size: 1rem;
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .group-image-container:hover .group-image {
        transform: scale(1.1);
    }

    .group-image-container:hover .overlay {
        background: linear-gradient(to bottom,
                rgba(0, 0, 0, 0.5),
                rgba(0, 0, 0, 0.8));
    }

    .group-image-container:hover .group-name {
        transform: translateY(-10px);
    }

    .group-image-container:hover .group-description {
        opacity: 1;
        transform: translateY(0);
    }
    
    /* ข้อความแจ้งเตือนไม่พบผลลัพธ์ */
    .no-results-message {
        text-align: center;
        padding: 40px 20px;
        background-color: #f8f9fa;
        border-radius: 10px;
        margin: 30px auto;
        max-width: 800px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    }
    
    .no-results-message h3 {
        color: #2C6FA8;
        margin-bottom: 15px;
    }
    
    .no-results-message p {
        color: #6c757d;
        font-size: 1.1rem;
        margin-bottom: 20px;
    }
    
    .no-results-message .btn {
        padding: 10px 25px;
        font-weight: 500;
    }
</style>

<!-- เริ่มต้นส่วน Header -->
<div class="container-fluid header-container">
    <div class="row header-row">
        <div class="col-lg-8 mx-auto text-center">
            <h1 class="display-4 fw-bold mb-4" style="color: white;">RESEARCH GROUP</h1>
            <form method="GET" action="{{ route('researchgroup') }}" class="search-form">
                <div class="input-group input-group-lg position-relative">
                    <input type="text" class="form-control search-input"
                        name="textsearch" value="{{ $search ?? '' }}"
                        placeholder="Search research group by name or description"
                        aria-label="Search research group">
                    <button type="submit" class="search-button">
                        <ion-icon name="search" size="large" class="search-icon"></ion-icon>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- สิ้นสุดส่วน Header -->

@if(isset($noResults) && $noResults)
<!-- แสดงข้อความเมื่อไม่พบผลลัพธ์ -->
<div class="no-results-message">
    <h3><ion-icon name="search-outline" class="me-2"></ion-icon> No Results Found</h3>
    <p>Sorry, we couldn't find any research groups matching "{{ $search }}".</p>
    <a href="{{ route('researchgroup') }}" class="btn btn-primary">
        <ion-icon name="refresh-outline" class="me-1"></ion-icon> Clear Search
    </a>
</div>
@else
<!-- ส่วนแสดง Research Group Cards -->
<div class="container">
    <div class="row row-cols-1 row-cols-md-3 g-4">
        @foreach ($resg as $rg)
        <div class="col research-group-item">
            <div class="card h-100">
                <a href="{{ route('researchgroupdetail', ['id' => $rg->id]) }}" class="text-decoration-none">
                    <div class="group-image-container">
                        <img src="{{ asset('img/'.$rg->group_image) }}" alt="Group Image" class="group-image">
                        <div class="overlay">
                            <h5 class="group-name">{{ $rg->{'group_name_'.app()->getLocale()} }}</h5>
                            <div class="group-description">
                                {{ Str::limit($rg->{'group_desc_'.app()->getLocale()}, 150) }}
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

@stop