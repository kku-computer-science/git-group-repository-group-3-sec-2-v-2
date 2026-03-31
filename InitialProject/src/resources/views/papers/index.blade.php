@extends('dashboards.users.layouts.user-dash-layout')
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.3/css/fixedHeader.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.0/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.3/css/fixedHeader.bootstrap4.min.css">

@section('title','Dashboard')

@section('content')
<div class="container">
    {{-- Display success message if available --}}
    @if ($message = Session::get('success'))
    <div class="alert alert-success">
        <p>{{ $message }}</p>
    </div>
    @endif

    <div class="card" style="padding: 16px;">
        <div class="card-body">
            <h4 class="card-title">Published Research</h4>

            {{-- Button to add a new Paper --}}
            <a class="btn btn-primary btn-menu btn-icon-text btn-sm mb-3" href="{{ route('papers.create') }}">
                <i class="mdi mdi-plus btn-icon-prepend"></i> Add
            </a>

            @if(Auth::user()->hasRole('teacher'))
            {{-- Button to fetch Papers from Scopus (encrypted user id is passed) --}}
            <a class="btn btn-primary btn-icon-text btn-sm mb-3"
               href="{{ route('callscopus', Crypt::encrypt(Auth::user()->id)) }}"
               onclick="showLoading()">
               <i class="mdi mdi-refresh btn-icon-prepend icon-sm"></i> Call Paper
            </a>
            @endif

            @if(Auth::user()->hasRole('admin'))
            <a class="btn btn-warning btn-icon-text btn-sm mb-3"
               href="{{ route('callscopus.all') }}"
               onclick="showLoading()">
               <i class="mdi mdi-account-multiple-outline btn-icon-prepend icon-sm"></i> Call All (Admin)
            </a>
            @endif

            <form action="{{ route('call.paper.doi') }}" method="POST" class="d-inline-block mb-3 ml-2" onsubmit="showLoading()">
                @csrf
                <div class="input-group input-group-sm">
                    <input type="text" name="doi" class="form-control" placeholder="Enter DOI (e.g. 10.1000/xyz)" required style="max-width: 250px;">
                    <div class="input-group-append">
                        <button class="btn btn-info" type="submit">
                            <i class="mdi mdi-download btn-icon-prepend"></i> Call by DOI
                        </button>
                    </div>
                </div>
            </form>
            <table id="example1" class="table table-striped">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Title</th>
                        <th>Type</th>
                        <th>Publication Year</th>
                        <th width="280px">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($papers as $i => $paper)
                    <tr>
                        <td>{{ $papers->firstItem() + $i }}</td>
                        <td>{{ safe_str_limit($paper->paper_name, 50) }}</td>
                        <td>{{ safe_str_limit($paper->paper_type, 50) }}</td>
                        <td>{{ $paper->paper_yearpub }}</td>
                        <td>
                            <form action="{{ route('papers.destroy', $paper->id) }}" method="POST">
                                <li class="list-inline-item">
                                    <a class="btn btn-outline-primary btn-sm" data-toggle="tooltip" title="View" href="{{ route('papers.show', $paper->id) }}">
                                       <i class="mdi mdi-eye"></i>
                                    </a>
                                </li>
                                @if(Auth::user()->can('update', $paper))
                                <li class="list-inline-item">
                                    <a class="btn btn-outline-success btn-sm" data-toggle="tooltip" title="Edit" href="{{ route('papers.edit', Crypt::encrypt($paper->id)) }}">
                                       <i class="mdi mdi-pencil"></i>
                                    </a>
                                </li>
                                @endif
                                {{-- Delete button (if needed, uncomment the following block) --}}
                                {{--
                                @csrf
                                @method('DELETE')
                                <li class="list-inline-item">
                                    <button class="btn btn-outline-danger btn-sm show_confirm" data-toggle="tooltip" title="Delete" type="submit">
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
            @include('partials.pagination', ['paginator' => $papers])
        </div>
    </div>
</div>

{{-- Popup for Imported Papers (when new papers are inserted) --}}
@if(session('importMessage'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        swal({
            title: 'Scopus & OpenAlex Import Result',
            text: {!! json_encode(session('importMessage')) !!},
            icon: 'success',
            button: 'OK'
        });
    });
</script>
@elseif(session('insertedPapers'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let inserted = @json(session('insertedPapers'));
        if (inserted && inserted.length > 0) {
            let message = "Imported Papers:\n";
            inserted.forEach(function(paper) {
                message += "- " + paper + "\n";
            });
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

{{-- Popup for Info message (when no new papers are inserted) --}}
@if(session('info'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        swal({
            title: 'Information',
            text: "{{ session('info') }}",
            icon: 'info',
            button: 'OK'
        });
    });
</script>
@endif

{{-- Popup for Error message --}}
@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        swal({
            title: 'Error',
            text: "{{ session('error') }}",
            icon: 'error',
            button: 'OK'
        });
    });
</script>
@endif

{{-- DataTables Scripts --}}
<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="http://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js" defer></script>
<script src="https://cdn.datatables.net/1.12.0/js/dataTables.bootstrap4.min.js" defer></script>
<script src="https://cdn.datatables.net/fixedheader/3.2.3/js/dataTables.fixedHeader.min.js" defer></script>

{{-- SweetAlert --}}
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<script>
    $(document).ready(function() {
        $('#example1').DataTable({
            order: [
                [3, 'desc']
            ],
            responsive: true,
            paging: false,
            info: false,
            searching: false
        });
    });

    // Delete confirmation script (if delete button is enabled)
    $('.show_confirm').click(function(event) {
        event.preventDefault();
        var form = $(this).closest("form");
        swal({
            title: "Are you sure?",
            text: "Once deleted, you will not be able to recover this data!",
            icon: "warning",
            buttons: true,
            dangerMode: true,
        }).then(function(willDelete) {
            if (willDelete) {
                swal("Data has been deleted!", { icon: "success", })
                .then(function() {
                    form.submit();
                });
            }
        });
    });
</script>
@stop
<script>
    function showLoading() {
        swal({
            title: 'Fetching Data...',
            text: 'System is querying Scopus & OpenAlex. Please wait...',
            icon: 'info',
            buttons: false,
            closeOnClickOutside: false,
            closeOnEsc: false
        });
    }
</script>
