@extends('dashboards.users.layouts.user-dash-layout')
@section('content')
<div class="container">
    @if ($message = Session::get('success'))
        <div class="alert alert-success">
            <p>{{ $message }}</p>
        </div>
    @endif

    <div class="card" style="padding: 16px;">
        <div class="card-body">
            <h4 class="card-title">กลุ่มวิจัย</h4>
            <a class="btn btn-primary btn-menu btn-icon-text btn-sm mb-3" href="{{ route('researchGroups.create') }}">
                <i class="mdi mdi-plus btn-icon-prepend"></i> ADD
            </a>
            <table id="example1" class="table table-striped">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Group name (ไทย)</th>
                        <th>Head</th>
                        <th>Member</th>
                        <th width="280px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($researchGroups as $i => $researchGroup)
                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ Str::limit($researchGroup->group_name_th, 50) }}</td>
                        <td>
                            @foreach($researchGroup->user as $user)
                                @if ($user->pivot->role == 1)
                                    {{ $user->fname_th }}
                                @endif
                            @endforeach
                        </td>
                        <td>
                            @foreach($researchGroup->user as $user)
                                @if ($user->pivot->role == 2)
                                    {{ $user->fname_th }}@if (!$loop->last), @endif
                                @endif
                            @endforeach
                        </td>
                        <td>
                            @php
                                // ดึง pivot ของ user (ที่ล็อกอิน) ในกลุ่มวิจัยปัจจุบัน
                                $authPivot = $researchGroup->user->where('id', Auth::id())->first();
                                
                                // เช็คเงื่อนไขใน pivot
                                $pivotRole  = optional($authPivot?->pivot)->role;     // role
                                $pivotCanEdit = optional($authPivot?->pivot)->can_edit; // can_edit
                                
                                // สร้างตัวแปร boolean ไว้ใช้งาน
                                $isHead    = ($pivotRole == 1);              // หัวหน้า
                                $canEdit   = ($pivotCanEdit == 1);           // can_edit
                                $isAdmin   = Auth::user()->hasRole('admin'); // มี role admin หรือไม่
                            @endphp

                            <!-- form สำหรับลบ -->
                            <form action="{{ route('researchGroups.destroy', $researchGroup->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')

                                <!-- ปุ่ม View (ทุกคนเห็นได้) -->
                                <a class="btn btn-outline-primary btn-sm" type="button"
                                   data-toggle="tooltip" data-placement="top" title="view"
                                   href="{{ route('researchGroups.show', $researchGroup->id) }}">
                                    <i class="mdi mdi-eye"></i>
                                </a>

                                <!-- กรณีเป็น admin หรือหัวหน้า => มี Edit & Delete -->
                                @if($isAdmin || $isHead)
                                    <a class="btn btn-outline-success btn-sm" type="button"
                                       data-toggle="tooltip" data-placement="top" title="Edit"
                                       href="{{ route('researchGroups.edit', $researchGroup->id) }}">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>
                                    <button class="btn btn-outline-danger btn-sm show_confirm" type="submit"
                                        data-toggle="tooltip" data-placement="top" title="Delete">
                                        <i class="mdi mdi-delete"></i>
                                    </button>

                                <!-- กรณีไม่ใช่ Admin/Head แต่ can_edit = 1 => มี Edit -->
                                @elseif($canEdit)
                                    <a class="btn btn-outline-success btn-sm" type="button"
                                       data-toggle="tooltip" data-placement="top" title="Edit"
                                       href="{{ route('researchGroups.edit', $researchGroup->id) }}">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>
                                @endif
                                <!-- ถ้าไม่เข้าเงื่อนไขอะไรเลย => ได้แค่ View -->
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.0/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.3/css/fixedHeader.bootstrap4.min.css">

<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="http://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js" defer></script>
<script src="https://cdn.datatables.net/1.12.0/js/dataTables.bootstrap4.min.js" defer></script>
<script src="https://cdn.datatables.net/fixedheader/3.2.3/js/dataTables.fixedHeader.min.js" defer></script>

<script>
$(document).ready(function() {
    $('#example1').DataTable({
        responsive: true,
    });
});

// สคริปต์ยืนยันการลบ
$('.show_confirm').click(function(event) {
    var form = $(this).closest("form");
    event.preventDefault();
    swal({
        title: "Are you sure?",
        text: "If you delete this, it will be gone forever.",
        icon: "warning",
        buttons: true,
        dangerMode: true,
    })
    .then((willDelete) => {
        if (willDelete) {
            swal("Delete Successfully", {
                icon: "success",
            }).then(function() {
                location.reload();
                form.submit();
            });
        }
    });
});
</script>
@stop
