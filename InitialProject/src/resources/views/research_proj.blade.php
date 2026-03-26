@extends('layouts.layout')
@section('content')

@include('partials.page-header', [
    'title' => 'โครงการบริการวิชาการ / โครงการวิจัย',
])

<div class="container pb-5">
    <div class="table-responsive">
        <table id="example1" class="table table-striped" style="width:100%">
            <thead>
                <tr>
                    <th style="font-weight:bold;white-space:nowrap;">ลำดับ</th>
                    <th class="col-md-1" style="font-weight:bold;white-space:nowrap;">ปี</th>
                    <th class="col-md-4" style="font-weight:bold;">ชื่อโครงการ</th>
                    <th class="col-md-4" style="font-weight:bold;">รายละเอียด</th>
                    <th class="col-md-2" style="font-weight:bold;white-space:nowrap;">ผู้รับผิดชอบ</th>
                    <th class="col-md-1" style="font-weight:bold;white-space:nowrap;">สถานะ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($resp as $i => $re)
                <tr>
                    <td style="vertical-align:top;text-align:left;">{{ $resp->firstItem() + $i }}</td>
                    <td style="vertical-align:top;text-align:left;">{{ ($re->project_year)+543 }}</td>
                    <td style="vertical-align:top;">{{ $re->project_name }}</td>
                    <td>
                        @if($re->project_start != null)
                        <div class="mb-2">
                            <strong>ระยะเวลา:</strong>
                            {{ \Carbon\Carbon::parse($re->project_start)->thaidate('j F Y') }}
                            ถึง {{ \Carbon\Carbon::parse($re->project_end)->thaidate('j F Y') }}
                        </div>
                        @endif
                        <div class="mb-2">
                            <strong>ประเภททุนวิจัย:</strong>
                            {{ $re->fund->fund_type ?? '-' }}
                        </div>
                        <div class="mb-2">
                            <strong>หน่วยงานที่สนับสนุน:</strong>
                            {{ $re->fund->support_resource ?? '-' }}
                        </div>
                        <div class="mb-2">
                            <strong>หน่วยงานที่รับผิดชอบ:</strong>
                            {{ $re->responsible_department }}
                        </div>
                        <div>
                            <strong>งบประมาณ:</strong>
                            {{ number_format($re->budget) }} บาท
                        </div>
                    </td>
                    <td style="vertical-align:top;">
                        @foreach($re->user as $user)
                            {{ $user->position_th }} {{ $user->fname_th }} {{ $user->lname_th }}<br>
                        @endforeach
                    </td>
                    <td style="vertical-align:top;">
                        @if($re->status == 1)
                            <span class="badge badge-success">ยื่นขอ</span>
                        @elseif($re->status == 2)
                            <span class="badge bg-warning text-dark">ดำเนินการ</span>
                        @else
                            <span class="badge bg-dark">ปิดโครงการ</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @include('partials.pagination', ['paginator' => $resp])
</div>

<script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap5.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap5.js"></script>

<script>
$(document).ready(function() {
    $('#example1').DataTable({
        order: [[1, 'desc']],
        responsive: true,
        paging: false,
        info: false,
        searching: false
    });
});
</script>
@stop
