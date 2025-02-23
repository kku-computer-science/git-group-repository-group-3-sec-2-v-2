@extends('layouts.layout')

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

    .blue-stripe {
        /* ใช้สีฟ้าเข้ม */
        background-color: #1075BB;
        /* เพิ่ม padding ให้สูงขึ้นเพื่อดูเต็มแบนเนอร์ */
        padding: 60px 20px;
        margin-bottom: 25px;
        text-align: center;
        /* จัดข้อความให้อยู่กลาง */
        color: #fff;
        /* ตัวหนังสือสีขาว */
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .blue-stripe h1 {
        color: #fff;
        /* ขยายขนาดฟอนต์ตามต้องการ */
        font-size: 2.4rem;
        font-weight: 600;
        margin: 0;
        line-height: 1.2;
    }

    /* Content boxes */
    .research-rationale-box {
        background-color: white;
        padding: 25px;
        margin-bottom: 25px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        border: 1px solid #eaeaea;
    }

    /* Headings */
    .research-rationale-box h2 {
        color: #003e80;
        font-size: 1.6rem;
        font-weight: 600;
        margin-bottom: 20px;
        padding-bottom: 10px;
        border-bottom: 1px solid #eaeaea;
    }

    .research-rationale-box h3 {
        color: #333;
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 0;
    }

    /* Member cards */
    /* .member-card {
        padding: 15px;
        margin-bottom: 20px;
        text-align: center;
        background-color: white;
        border: 1px solid #eaeaea;
        height: 100%;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
    } */

    .head-lab-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        background-color: #003e80;
        color: white;
        padding: 4px 10px;
        font-size: 0.85rem;
        font-weight: 500;
    }

    /* Image styling */
    /* .center-image {
        width: 100%;
        max-width: 200px;
        height: auto;
        margin-bottom: 15px;
        border: 1px solid #eaeaea;
        object-fit: contain;
        aspect-ratio: 3/4;
    } */

    .member-card {
        width: 200px;
        /* กำหนดความกว้างของการ์ดเท่ากันทุกใบ */
        margin: 0 auto;
        /* จัดกึ่งกลาง */
        /* ความสูงปล่อย auto ให้ปรับตามเนื้อหา */
    }

    .center-image {
        width: 100%;
        /* ให้รูปกว้างเต็มการ์ด */
        height: auto;
        /* ปล่อยความสูงตามสัดส่วน */
        object-fit: contain;
        /* หรือ cover ตามต้องการ */
        border: 1px solid #eaeaea;
    }


    /* Profile link styles */
    .profile-link {
        display: inline-block;
        text-decoration: none;
    }

    /* Person info */
    .person-info {
        text-align: center;
        margin-top: 12px;
    }

    .person-info p {
        color: #333;
        font-size: 1.1rem;
        font-weight: 500;
        margin: 5px 0;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .blue-stripe h1 {
            font-size: 1.8rem;
        }

        .research-rationale-box {
            padding: 20px;
        }

        .research-rationale-box h2 {
            font-size: 1.4rem;
        }

        .center-image {
            max-width: 160px;
        }
    }

    .research-rationale-box h3 {
        color: #003E80;
    }

    .research-rationale-box h4 {
        color: #414141;
        /* สีเทาเข้ม */
        font-size: 1rem;
        /* ปรับขนาดฟอนต์เล็กลง (เดิม h4 ใหญ่กว่า 1rem) */
        line-height: 1.4;
        /* ปรับระยะห่างบรรทัดให้เหมาะสม */
    }
</style>

@section('content')
@foreach ($resgd as $rg)
<!-- Blue Stripe with Group Name -->
<div class="blue-stripe">
    <!-- ใส่ชื่อกลุ่มวิจัย -->
    <h1>{{ $rg->{'group_name_'.app()->getLocale()} }}</h1>
</div>

<div class="container-fluid px-4">


    <!-- Research Rationale -->
    <div class="research-rationale-box">
        <h2>Research Rationale</h2>
        <h4>{{ $rg->{'group_desc_'.app()->getLocale()} }}</h4>
    </div>

    <!-- Main Research Areas / Topics -->
    <div class="research-rationale-box">
        <h2>Main Research Areas / Topics</h2>
        <h4>{{ $rg->{'main_research_'.app()->getLocale()} }}</h4>
    </div>

    <!-- Researcher Details -->
    <div class="research-rationale-box">
        <h2>Researcher Details</h2>
        <h4>{{ $rg->{'group_detail_'.app()->getLocale()} }}</h4>
    </div>

    <!-- Research Group Members (Teachers) -->
    <div class="research-rationale-box">
        <!-- หัวข้อใหญ่แสดงครั้งเดียว -->
        <h2 class="text-center">Member Of Research Group</h2>

        <!-- (1) Member (Teacher: role = 1 or 2) -->
        <h3 class="mt-4">Member</h3>
        <!-- แถวแรก: Head LAB (role = 1) เพียงคนเดียวตรงกลาง -->
        <div class="row justify-content-center g-4 mb-4">
            @foreach($rg->user as $r)
            @if($r->hasRole('teacher') && isset($r->pivot) && $r->pivot->role == 1)
            <div class="col-auto">
                <div class="member-card">
                    <div class="head-lab-badge">Head LAB</div>
                    <a href="{{ route('detail', Crypt::encrypt($r->id)) }}" class="profile-link">
                        <img src="{{ $r->picture ?? asset('img/default-profile.png') }}"
                            alt="{{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}"
                            class="center-image">
                    </a>
                    <div class="person-info">
                        @if(app()->getLocale() == 'en' && $r->academic_ranks_en == 'Lecturer' && $r->doctoral_degree == 'Ph.D.')
                        <p>{{ $r->fname_en }} {{ $r->lname_en }}, Ph.D.</p>
                        @elseif(app()->getLocale() == 'en' && $r->academic_ranks_en == 'Lecturer')
                        <p>{{ $r->fname_en }} {{ $r->lname_en }}</p>
                        @elseif(app()->getLocale() == 'en' && $r->doctoral_degree == 'Ph.D.')
                        <p>{{ str_replace('Dr.', ' ', $r->position_en) }} {{ $r->fname_en }} {{ $r->lname_en }}, Ph.D.</p>
                        @else
                        <p>{{ $r->{'position_'.app()->getLocale()} }} {{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>

        <!-- แถวถัดมา: สมาชิกคนอื่น (role = 2) -->
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4">
            @foreach($rg->user as $r)
            @if($r->hasRole('teacher') && isset($r->pivot) && $r->pivot->role == 2)
            <div class="col">
                <div class="member-card">
                    <a href="{{ route('detail', Crypt::encrypt($r->id)) }}" class="profile-link">
                        <img src="{{ $r->picture ?? asset('img/default-profile.png') }}"
                            alt="{{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}"
                            class="center-image">
                    </a>
                    <div class="person-info">
                        @if(app()->getLocale() == 'en' && $r->academic_ranks_en == 'Lecturer' && $r->doctoral_degree == 'Ph.D.')
                        <p>{{ $r->fname_en }} {{ $r->lname_en }}, Ph.D.</p>
                        @elseif(app()->getLocale() == 'en' && $r->academic_ranks_en == 'Lecturer')
                        <p>{{ $r->fname_en }} {{ $r->lname_en }}</p>
                        @elseif(app()->getLocale() == 'en' && $r->doctoral_degree == 'Ph.D.')
                        <p>{{ str_replace('Dr.', ' ', $r->position_en) }} {{ $r->fname_en }} {{ $r->lname_en }}, Ph.D.</p>
                        @else
                        <p>{{ $r->{'position_'.app()->getLocale()} }} {{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>

        <!-- (2) Postdoctoral Researcher (role = 3) -->
        <h3 class="mt-5">Postdoctoral Researcher</h3>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4 justify-content-center">
            @foreach($rg->user as $r)
            @if(isset($r->pivot) && $r->pivot->role == 3)
            <div class="col">
                <div class="member-card">
                    <a href="{{ route('detail', Crypt::encrypt($r->id)) }}" class="profile-link">
                        <img src="{{ $r->picture ?? asset('img/default-profile.png') }}"
                            alt="{{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}"
                            class="center-image">
                    </a>
                    <div class="person-info">
                        @if(app()->getLocale() == 'en' && $r->doctoral_degree == 'Ph.D.')
                        <p>{{ $r->fname_en }} {{ $r->lname_en }}, Ph.D.</p>
                        @else
                        <p>{{ $r->{'position_'.app()->getLocale()} }} {{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>

        <!-- Students (รวม Ph.D. (4), Master's (5), Undergrad (6)) -->
        <h3 class="mt-5">Students</h3>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4 justify-content-center">
            @foreach($rg->user as $r)
            @if(isset($r->pivot) && in_array($r->pivot->role, [4, 5, 6]))
            <div class="col">
                <div class="member-card">
                    <a href="{{ route('detail', Crypt::encrypt($r->id)) }}" class="profile-link">
                        <img src="{{ $r->picture ?? asset('img/default-profile.png') }}"
                            alt="{{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}"
                            class="center-image">
                    </a>
                    <div class="person-info">
                        <!-- แสดงข้อมูลตาม locale -->
                        <p>{{ $r->{'position_'.app()->getLocale()} }} {{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}</p>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>

        <!-- (6) Visiting Scholars (role = 7) -->
        <h3 class="mt-5">Visiting Scholars</h3>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-4 justify-content-center">
            @foreach($rg->user as $r)
            @if(isset($r->pivot) && $r->pivot->role == 7)
            <div class="col">
                <div class="member-card">
                    <a href="{{ route('detail', Crypt::encrypt($r->id)) }}" class="profile-link">
                        <img src="{{ $r->picture ?? asset('img/default-profile.png') }}"
                            alt="{{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}"
                            class="center-image">
                    </a>
                    <div class="person-info">
                        <p>{{ $r->{'position_'.app()->getLocale()} }} {{ $r->{'fname_'.app()->getLocale()} }} {{ $r->{'lname_'.app()->getLocale()} }}</p>
                    </div>
                </div>
            </div>
            @endif
            @endforeach
        </div>

    </div>



    @endforeach
</div>
@stop