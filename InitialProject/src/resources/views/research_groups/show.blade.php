@extends('dashboards.users.layouts.user-dash-layout')
@section('content')
@php
    use Illuminate\Support\Facades\DB;
    use App\Models\Author;
@endphp
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
                        @if($user->pivot->role == 2 && !$user->hasRole('student'))
                            {{ $user->position_th ?? '' }} {{ $user->fname_th }} {{ $user->lname_th }}@if(!$loop->last), @endif
                        @endif
                    @endforeach
                </p>
            </div>

            <!-- นักศึกษา -->
            <div class="row mt-2">
                <p class="card-text col-sm-3"><b>นักศึกษา</b></p>
                <p class="card-text col-sm-9">
                    @foreach($researchGroup->user as $user)
                        @if($user->pivot->role == 2 && $user->hasRole('student'))
                            {{ $user->position_th ?? '' }} {{ $user->fname_th }} {{ $user->lname_th }}@if(!$loop->last), @endif
                        @endif
                    @endforeach
                </p>
            </div>

            <!-- นักวิจัยหลังปริญญาเอก -->
            <div class="row mt-2">
                <p class="card-text col-sm-3"><b>นักวิจัยหลังปริญญาเอก (ภายใน)</b></p>
                <p class="card-text col-sm-9">
                    @php
                        $hasPostdocInternal = false;
                    @endphp
                    @foreach($researchGroup->user as $user)
                        @if($user->pivot->role == 3)
                            @php $hasPostdocInternal = true; @endphp
                            {{ $user->position_th ?? '' }} {{ $user->fname_th }} {{ $user->lname_th }}@if(!$loop->last), @endif
                        @endif
                    @endforeach
                    @if(!$hasPostdocInternal)
                        <em>ไม่มีข้อมูล</em>
                    @endif
                </p>
            </div>

            <!-- นักวิจัยหลังปริญญาเอก (ภายนอก) -->
            <div class="row mt-2">
                <p class="card-text col-sm-3"><b>นักวิจัยหลังปริญญาเอก (ภายนอก)</b></p>
                <p class="card-text col-sm-9">
                    @php
                        $postdocExternal = [];
                        $extPostdocs = DB::table('work_of_research_groups')
                            ->where('research_group_id', $researchGroup->id)
                            ->where('role', 3)
                            ->whereNull('user_id')
                            ->whereNotNull('author_id')
                            ->get();
                        
                        // ดึงข้อมูล author_id เพื่อหลีกเลี่ยงการซ้ำซ้อน
                        $processedAuthorIds = [];
                            
                        foreach($extPostdocs as $extPostdoc) {
                            // ข้ามถ้า author_id นี้ถูกประมวลผลไปแล้ว
                            if (in_array($extPostdoc->author_id, $processedAuthorIds)) {
                                continue;
                            }
                            
                            $author = Author::find($extPostdoc->author_id);
                            if($author) {
                                $postdocExternal[] = $author;
                                $processedAuthorIds[] = $extPostdoc->author_id;
                            }
                        }
                    @endphp
                    
                    @if(count($postdocExternal) > 0)
                        @foreach($postdocExternal as $scholar)
                            {{ $scholar->academic_ranks_th ?? '' }} {{ $scholar->author_fname }} {{ $scholar->author_lname }}@if(!$loop->last), @endif
                        @endforeach
                    @else
                        <em>ไม่มีข้อมูล</em>
                    @endif
                </p>
            </div>

            <!-- นักวิจัยรับเชิญ -->
            <div class="row mt-2">
                <p class="card-text col-sm-3"><b>นักวิจัยรับเชิญ</b></p>
                <p class="card-text col-sm-9">
                    @php
                        $visitingScholars = [];
                        foreach($researchGroup->visitingScholars as $scholar) {
                            $pivotData = $researchGroup->visitingScholars()->where('author_id', $scholar->id)->first()->pivot;
                            if ($pivotData->role == 4) {
                                $visitingScholars[] = $scholar;
                            }
                        }
                    @endphp
                    
                    @if(count($visitingScholars) > 0)
                        @foreach($visitingScholars as $scholar)
                            {{ $scholar->academic_ranks_th ?? '' }} {{ $scholar->author_fname }} {{ $scholar->author_lname }}@if(!$loop->last), @endif
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