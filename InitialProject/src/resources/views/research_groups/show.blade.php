@extends('dashboards.users.layouts.user-dash-layout')
@section('content')
<div class="container">
    <div class="card col-md-10" style="padding: 16px;">
        <div class="card-body">
            <h4 class="card-title">รายละเอียดกลุ่มวิจัย</h4>
            <p class="card-description">ข้อมูลรายละเอียดกลุ่มวิจัย</p>
            <!-- กลุ่มวิจัยพื้นฐาน -->
            <div class="row mt-2">
                <p class="card-text col-sm-3"><b>ชื่อกลุ่มวิจัย (ภาษาไทย)</b></p>
                <p class="card-text col-sm-9">{{ $researchGroup->group_name_th }}</p>
            </div>
            <div class="row mt-1">
                <p class="card-text col-sm-3"><b>ชื่อกลุ่มวิจัย (English)</b></p>
                <p class="card-text col-sm-9">{{ $researchGroup->group_name_en }}</p>
            </div>
            <div class="row mt-2">
                <p class="card-text col-sm-3"><b>คำอธิบายกลุ่มวิจัย (ภาษาไทย)</b></p>
                <p class="card-text col-sm-9">{{ $researchGroup->group_desc_th }}</p>
            </div>
            <div class="row mt-2">
                <p class="card-text col-sm-3"><b>คำอธิบายกลุ่มวิจัย (English)</b></p>
                <p class="card-text col-sm-9">{{ $researchGroup->group_desc_en }}</p>
            </div>
            <div class="row mt-2">
                <p class="card-text col-sm-3"><b>รายละเอียดกลุ่มวิจัย (ภาษาไทย)</b></p>
                <p class="card-text col-sm-9">{{ $researchGroup->group_detail_th }}</p>
            </div>
            <div class="row mt-2">
                <p class="card-text col-sm-3"><b>รายละเอียดกลุ่มวิจัย (English)</b></p>
                <p class="card-text col-sm-9">{{ $researchGroup->group_detail_en }}</p>
            </div>

            <!-- หัวหน้ากลุ่ม -->
            <div class="row mt-3">
                <p class="card-text col-sm-3"><b>หัวหน้ากลุ่มวิจัย</b></p>
                <p class="card-text col-sm-9">
                    @foreach($researchGroup->user as $user)
                        @if($user->pivot->role == 1)
                            {{ $user->position_th ?? '' }} {{ $user->fname_th }} {{ $user->lname_th }}
                        @endif
                    @endforeach
                </p>
            </div>

            <!-- สมาชิกกลุ่ม -->
            <div class="row mt-2">
                <p class="card-text col-sm-3"><b>สมาชิกกลุ่มวิจัย</b></p>
                <p class="card-text col-sm-9">
                    @foreach($researchGroup->user as $user)
                        @if($user->pivot->role == 2)
                            {{ $user->position_th ?? '' }} {{ $user->fname_th }} {{ $user->lname_th }}@if(!$loop->last), @endif
                        @endif
                    @endforeach
                </p>
            </div>

            <!-- นักวิจัยรับเชิญ -->
            <div class="row mt-2">
                <p class="card-text col-sm-3"><b>นักวิจัยรับเชิญ</b></p>
                <p class="card-text col-sm-9">
                    @if($researchGroup->visitingScholars->isNotEmpty())
                        @foreach($researchGroup->visitingScholars as $scholar)
                            {{ $scholar->author_fname }} {{ $scholar->author_lname }}@if(!$loop->last), @endif
                        @endforeach
                    @else
                        <em>ไม่มีข้อมูล</em>
                    @endif
                </p>
            </div>

            <a class="btn btn-primary mt-5" href="{{ route('researchGroups.index') }}">Back</a>
        </div>
    </div>
</div>
@stop

@section('javascript')
<script>
$(document).ready(function() {
    /* ตัวอย่างสคริปต์เพิ่มเติมหากมีการใช้ modal หรือฟังก์ชันอื่น ๆ */
});
</script>
@stop
