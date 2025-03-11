@extends('dashboards.users.layouts.user-dash-layout')
@section('content')
@php
    use Illuminate\Support\Facades\DB;
@endphp
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
                        <th>Visiting</th>
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
                                @if ($user->pivot->role == 2 || $user->pivot->role == 3) 
                                    {{ $user->fname_th }}@if (!$loop->last), @endif
                                @endif
                            @endforeach
                        </td>
                        <td>
                            @php
                                $visitingScholars = [];
                                $postdocScholars = [];
                                
                                // แยก Postdoctoral ออกจาก Visiting Scholars
                                foreach($researchGroup->visitingScholars as $visiting) {
                                    $pivotData = $researchGroup->visitingScholars()->where('author_id', $visiting->id)->first()->pivot;
                                    if ($pivotData->role == 4) {
                                        $visitingScholars[] = $visiting;
                                    } else if ($pivotData->role == 3) {
                                        $postdocScholars[] = $visiting;
                                    }
                                }
                                
                                // ดึงข้อมูล Postdoctoral ภายนอกที่อาจตกหล่น
                                $processedAuthorIds = array_map(function($scholar) {
                                    return $scholar->id;
                                }, $postdocScholars);
                                
                                // ล้างอาร์เรย์ postdocScholars เพื่อป้องกันการซ้ำซ้อน
                                $postdocScholars = [];
                                $uniqueAuthors = [];
                                
                                $extPostdocs = DB::table('work_of_research_groups')
                                    ->where('research_group_id', $researchGroup->id)
                                    ->where('role', 3)
                                    ->whereNull('user_id')
                                    ->whereNotNull('author_id')
                                    ->get();
                                    
                                foreach($extPostdocs as $extPostdoc) {
                                    // ข้ามหากมี author_id ซ้ำกัน
                                    if (in_array($extPostdoc->author_id, $uniqueAuthors)) {
                                        continue;
                                    }
                                    
                                    $author = \App\Models\Author::find($extPostdoc->author_id);
                                    if($author) {
                                        $postdocScholars[] = $author;
                                        $uniqueAuthors[] = $extPostdoc->author_id;
                                    }
                                }
                            @endphp
                            
                            @if(count($visitingScholars) > 0)
                                <strong>Visiting Scholars:</strong><br>
                                @foreach($visitingScholars as $visiting)
                                    {{ $visiting->author_fname }} @if(!$loop->last), @endif
                                @endforeach
                            @endif
                            
                            @if(count($postdocScholars) > 0)
                                @if(count($visitingScholars) > 0)<br>@endif
                                <strong>Postdoctoral (ภายนอก):</strong><br>
                                @foreach($postdocScholars as $postdoc)
                                    {{ $postdoc->author_fname }} @if(!$loop->last), @endif
                                @endforeach
                            @endif
                            
                            @if(count($visitingScholars) == 0 && count($postdocScholars) == 0)
                                -
                            @endif
                        </td>
                        <td>
                            @php
                                $authPivot = $researchGroup->user->where('id', Auth::id())->first();
                                
                                $pivotRole  = optional($authPivot?->pivot)->role;
                                $pivotCanEdit = optional($authPivot?->pivot)->can_edit;
                                
                                $isHead    = ($pivotRole == 1);
                                $canEdit   = ($pivotCanEdit == 1);
                                $isAdmin   = Auth::user()->hasRole('admin');
                            @endphp

                            <form action="{{ route('researchGroups.destroy', $researchGroup->id) }}" method="POST" style="display:inline-block;">
                                @csrf
                                @method('DELETE')

                                <a class="btn btn-outline-primary btn-sm" type="button"
                                   data-toggle="tooltip" data-placement="top" title="view"
                                   href="{{ route('researchGroups.show', $researchGroup->id) }}">
                                    <i class="mdi mdi-eye"></i>
                                </a>

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
                                @elseif($canEdit)
                                    <a class="btn btn-outline-success btn-sm" type="button"
                                       data-toggle="tooltip" data-placement="top" title="Edit"
                                       href="{{ route('researchGroups.edit', $researchGroup->id) }}">
                                        <i class="mdi mdi-pencil"></i>
                                    </a>
                                @endif
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

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
