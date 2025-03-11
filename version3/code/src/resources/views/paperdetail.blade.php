@extends('layouts.layout')

@section('content')
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<style>
    .back-container {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        margin-top: 20px;
        /* ลดระยะห่างจากด้านบน */
        margin-left: 20px;
        /* เว้นระยะจากซ้าย */
    }

    .btn-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: linear-gradient(135deg, #007bff, #0056b3);
        color: white;
        font-size: 18px;
        font-weight: bold;
        padding: 12px 24px;
        border: none;
        border-radius: 30px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        transition: all 0.3s ease-in-out;
        cursor: pointer;
        text-transform: uppercase;
    }

    .btn-back:hover {
        background: linear-gradient(135deg, #0056b3, #007bff);
        transform: scale(1.05);
        box-shadow: 0px 6px 12px rgba(0, 0, 0, 0.3);
    }

    .btn-back:active {
        transform: scale(0.95);
    }

    .card-image {
        width: 200px;
        /* กำหนดความกว้าง */
        height: 300px;
        /* กำหนดความสูง */
        border-radius: 10px;
        /* เพิ่มมุมโค้ง */
        object-fit: cover;
        /* ทำให้รูปเต็มกรอบโดยไม่เสียสัดส่วน */
        display: block;
        /* ป้องกัน margin ด้านล่างของ <img> */
    }

    h3 {
        color: #1075BB;
    }
</style>

<div class="container">

    <button onclick="history.back()" class="btn-back">
        ← ย้อนกลับ
    </button>

    <div class="row align-items-start" style="margin-top: 40px;">
        <!-- Image and Author Info -->
        <div class="col-md-3 text-center d-flex flex-column align-items-center">
            @if($userimg && $userimg->picture)
            <img class="card-image" src="{{ asset('images/imag_user/' . $userimg->picture) }}" alt="">
            @else
            <img class="card-image" src="{{ asset('images/imag_user/no-image.jpg') }}" alt="">
            @endif
            @if($authors->isNotEmpty())
            <h5 class="mt-3 font-weight-bold text-dark">
                <!-- <a href="javascript:history.back()"><u>{{ $authors->first()->author_fname }} {{ $authors->first()->author_lname }}, Ph.D.</u></a> -->
            </h5>
            @else
            <h5 class="mt-3 font-weight-bold text-danger">
                No author found
            </h5>
            @endif
        </div>

        <!-- Paper Information -->
        <div class="col-md-9">
            <h3 class="font-weight-bold mb-3">{{ $paper->paper_name }}</h3>
            <table class="table table-borderless text-muted">
                <tbody>
                    <tr>
                        <th class="text-right" style="width: 25%">ผู้เขียน</th>
                        <td class="font-weight-bold text-dark">
                            @foreach($authors as $author)
                            {{ $author->author_fname }} {{ $author->author_lname }}@if(!$loop->last), @endif
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th class="text-right">ปีที่เผยแพร่</th>
                        <td>{{ date('Y', strtotime($paper->created_at)) }}</td>
                    </tr>
                    <tr>
                        <th class="text-right">ประเภทบทความ</th>
                        <td>{{ $paper->paper_type }}</td>
                    </tr>
                    <tr>
                        <th class="text-right">คำสำคัญ</th>
                        <td>
                            @if(is_array($paper->keyword))
                                @foreach($paper->keyword as $keyword)
                                    {{ $keyword['$'] ?? '' }}@if(!$loop->last), @endif
                                @endforeach
                            @else
                                {{ $paper->keyword }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th class="text-right">วารสารวิชาการ</th>
                        <td>{{ $paper->paper_sourcetitle }}</td>
                    </tr>
                    <tr>
                        <th class="text-right">เล่มที่</th>
                        <td>{{ $paper->paper_volume }}</td>
                    </tr>
                    <tr>
                        <th class="text-right">หน้า</th>
                        <td>{{ $paper->paper_page }}</td>
                    </tr>
                    <tr>
                        <th class="text-right">ผู้เผยแพร่</th>
                        <td>{{ $paper->publication }}</td>
                    </tr>
                    @if($paper->paper_funder)
                    <tr>
                        <th class="text-right">แหล่งทุน</th>
                        <td>
                            @if(is_array($paper->paper_funder))
                                @foreach($paper->paper_funder as $funder)
                                    {{ $funder['$'] ?? '' }}@if(!$loop->last)<br><br>@endif
                                @endforeach
                            @else
                                {{ $paper->paper_funder }}
                            @endif
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <th class="text-right">คำอธิบาย</th>
                        <td class="text-justify text-dark">{{ $paper->abstract }}</td>
                    </tr>
                    <tr>
                        <th class="text-right">ลิงก์บทความ</th>
                        <td>
                            @if($paper->paper_url)
                            <a href="{{ $paper->paper_url }}" target="_blank" class="text-primary">คลิกเพื่อดูบทความ <i class="fas fa-external-link-alt"></i></a>
                            @else
                            <span class="text-muted">ไม่มีลิงก์บทความ</span>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            <p class="text-muted mt-3">
                <strong>การอ้างอิงทั้งหมด</strong> อ้างโดย {{ $paper->paper_citation }}
            </p>
        </div>
    </div>
</div>
@endsection