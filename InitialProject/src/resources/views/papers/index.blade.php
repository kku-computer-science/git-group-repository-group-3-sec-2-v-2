@extends('dashboards.users.layouts.user-dash-layout')
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.3/css/fixedHeader.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.0/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.3/css/fixedHeader.bootstrap4.min.css">

@section('title','Dashboard')

@section('content')
<div class="container">
    {{-- แสดง Message ทั่วไป --}}
    @if ($message = Session::get('success'))
    <div class="alert alert-success">
        <p>{{ $message }}</p>
    </div>
    @endif

    <div class="card" style="padding: 16px;">
        <div class="card-body">
            <h4 class="card-title">Published research</h4>

            {{-- ปุ่ม Add Paper --}}
            <a class="btn btn-primary btn-menu btn-icon-text btn-sm mb-3" href="{{ route('papers.create') }}">
                <i class="mdi mdi-plus btn-icon-prepend"></i> ADD
            </a>

            @if(Auth::user()->hasRole('teacher'))
            {{-- ปุ่ม Call Paper (Scopus) -> ส่งค่าเป็น encrypt ของ user->id --}}
            <a class="btn btn-primary btn-icon-text btn-sm mb-3"
               href="{{ route('callscopus', Crypt::encrypt(Auth::user()->id)) }}">
               <i class="mdi mdi-refresh btn-icon-prepend icon-sm"></i> Call Paper
            </a>
            @endif

            <table id="example1" class="table table-striped">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>ชื่อเรื่อง</th>
                        <th>ประเภท</th>
                        <th>ปีที่ตีพิมพ์</th>
                        <th width="280px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($papers->sortByDesc('paper_yearpub') as $i => $paper)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ Str::limit($paper->paper_name, 50) }}</td>
                        <td>{{ Str::limit($paper->paper_type, 50) }}</td>
                        <td>{{ $paper->paper_yearpub }}</td>
                        <td>
                            <form action="{{ route('papers.destroy', $paper->id) }}" method="POST">
                                <li class="list-inline-item">
                                    <a class="btn btn-outline-primary btn-sm" type="button"
                                       data-toggle="tooltip" data-placement="top" title="view"
                                       href="{{ route('papers.show', $paper->id) }}">
                                       <i class="mdi mdi-eye"></i>
                                    </a>
                                </li>
                                @if(Auth::user()->can('update',$paper))
                                <li class="list-inline-item">
                                    <a class="btn btn-outline-success btn-sm" type="button"
                                       data-toggle="tooltip" data-placement="top" title="Edit"
                                       href="{{ route('papers.edit', Crypt::encrypt($paper->id)) }}">
                                       <i class="mdi mdi-pencil"></i>
                                    </a>
                                </li>
                                @endif
                                {{-- ลบ (comment ออกหากยังไม่ใช้งาน) --}}
                                {{-- 
                                @csrf
                                @method('DELETE')
                                <li class="list-inline-item">
                                    <button class="btn btn-outline-danger btn-sm show_confirm"
                                            type="submit" data-toggle="tooltip"
                                            data-placement="top" title="Delete">
                                        <i class="mdi mdi-delete"></i>
                                    </button>
                                </li>
                                --}}
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <br>
        </div>
    </div>
</div>

{{-- หาก Controller ส่ง insertedPapers มา จะอยู่ใน session --}}
@if(session('insertedPapers'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let inserted = @json(session('insertedPapers'));

        // ถ้ามีรายการที่ถูก insert
        if (inserted && inserted.length > 0) {
            let message = "Inserted papers:\n";
            inserted.forEach(paper => {
                message += "- " + paper + "\n";
            });

            // แสดง popup ด้วย sweetalert
            swal({
                title: 'Scopus Import Result',
                text: message,
                icon: 'success',
                button: 'OK'
            });
        }
    });
</script>
@endif

{{-- Scripts DataTables --}}
<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="http://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js" defer></script>
<script src="https://cdn.datatables.net/1.12.0/js/dataTables.bootstrap4.min.js" defer></script>
<script src="https://cdn.datatables.net/fixedheader/3.2.3/js/dataTables.fixedHeader.min.js" defer></script>

{{-- sweetalert (หากยังไม่มี) --}}
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script>
    $(document).ready(function() {
        var table1 = $('#example1').DataTable({
            responsive: true,
        });
    });

    // ตัวอย่างสคริปต์ยืนยันการลบ
    $('.show_confirm').click(function(event) {
        var form = $(this).closest("form");
        event.preventDefault();
        swal({
            title: `Are you sure?`,
            text: "If you delete this, it will be gone forever.",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        })
        .then((willDelete) => {
            if (willDelete) {
                swal("Delete Successfully", { icon: "success", })
                .then(function() {
                    location.reload();
                    form.submit();
                });
            }
        });
    });
</script>
@stop
