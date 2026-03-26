@extends('layouts.layout')

@section('content')
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

{{-- Styles moved to style.css (.btn-back, .paper-detail-img, h3 color) --}}

<div class="container pb-5">

    <button onclick="history.back()" class="btn-back">← ย้อนกลับ</button>

    <div class="row align-items-start mt-4 g-4">
        <!-- Image and Author Info -->
        <div class="col-md-3 text-center d-flex flex-column align-items-center">
            @if($userimg && $userimg->picture)
            <img class="paper-detail-img" src="{{ asset('images/imag_user/' . $userimg->picture) }}" alt="">
            @else
            <img class="paper-detail-img" src="{{ asset('images/imag_user/no-image.jpg') }}" alt="">
            @endif
            @if($authors->isNotEmpty())
            <h5 class="mt-3 fw-bold text-dark">
                <!-- <a href="javascript:history.back()"><u>{{ $authors->first()->author_fname }} {{ $authors->first()->author_lname }}, Ph.D.</u></a> -->
            </h5>
            @else
            <h5 class="mt-3 fw-bold text-danger">
                No author found
            </h5>
            @endif
        </div>

        <!-- Paper Information -->
        <div class="col-md-9">
            <h3 class="fw-bold mb-3">{{ $paper->paper_name }}</h3>
            <div class="table-responsive">
            <table class="table table-borderless text-muted">
                <tbody>
                    <tr>
                        <th class="text-end" style="width: 25%">ผู้เขียน</th>
                        <td class="fw-bold text-dark">
                            @foreach($authors as $author)
                            {{ $author->author_fname }} {{ $author->author_lname }}@if(!$loop->last), @endif
                            @endforeach
                        </td>
                    </tr>
                    <tr>
                        <th class="text-end">ปีที่เผยแพร่</th>
                        <td>{{ $paper->paper_yearpub ?? date('Y', strtotime($paper->created_at)) }}</td>
                    </tr>
                    <tr>
                        <th class="text-end">ประเภทบทความ</th>
                        <td>{{ $paper->paper_type }}</td>
                    </tr>
                    <tr>
                        <th class="text-end">คำสำคัญ</th>
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
                        <th class="text-end">วารสารวิชาการ</th>
                        <td>{{ $paper->paper_sourcetitle }}</td>
                    </tr>
                    <tr>
                        <th class="text-end">เล่มที่</th>
                        <td>{{ $paper->paper_volume }}</td>
                    </tr>
                    <tr>
                        <th class="text-end">หน้า</th>
                        <td>{{ $paper->paper_page }}</td>
                    </tr>
                    <tr>
                        <th class="text-end">ผู้เผยแพร่</th>
                        <td>{{ $paper->publication }}</td>
                    </tr>
                    @if($paper->paper_funder)
                    <tr>
                        <th class="text-end">แหล่งทุน</th>
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
                        <th class="text-end">คำอธิบาย</th>
                        <td class="text-dark" style="text-align: justify;">{{ $paper->abstract }}</td>
                    </tr>
                    <tr>
                        <th class="text-end">ลิงก์บทความ</th>
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
            </div>
            <p class="text-muted mt-3">
                <strong>การอ้างอิงทั้งหมด</strong> อ้างโดย {{ $paper->paper_citation }}
            </p>
        </div>
    </div>
</div>
@endsection
