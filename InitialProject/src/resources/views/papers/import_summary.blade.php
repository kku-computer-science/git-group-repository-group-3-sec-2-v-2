@extends('dashboards.users.layouts.user-dash-layout')
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.2.3/css/fixedHeader.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/1.12.0/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.1.0/css/fixedColumns.bootstrap4.min.css">

@section('content')
<div class="container">
    <div class="row">
        <div class="col-lg-12 margin-tb">
            <div class="pull-left mb-4">
                <h2>Scopus & OpenAlex Bulk Import Summary</h2>
            </div>
            <div class="pull-right mb-4">
                <a class="btn btn-primary" href="{{ route('papers.index') }}"> Back to Papers</a>
            </div>
        </div>
    </div>

    @if ($message = Session::get('success'))
    <div class="alert alert-success">
        <p>{{ $message }}</p>
    </div>
    @endif

    <div class="card" style="padding: 16px;">
        <div class="card-body">
            <h4 class="card-title">Import Results by Researcher</h4>
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="summary-table">
                    <thead>
                        <tr>
                            <th width="20%">Researcher</th>
                            <th width="10%">Total New</th>
                            <th width="35%">✅ Complete Papers (Abstract + Keywords)</th>
                            <th width="35%">⚠️ Incomplete Papers (Missing Data)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($summary as $userName => $result)
                            @if(count($result['complete']) > 0 || count($result['incomplete']) > 0)
                            <tr>
                                <td><strong>{{ $userName }}</strong></td>
                                <td>
                                    <span class="badge badge-success" style="font-size: 14px;">
                                        {{ count($result['complete']) + count($result['incomplete']) }}
                                    </span>
                                </td>
                                <td>
                                    @if(count($result['complete']) > 0)
                                        <ul style="padding-left: 20px; margin-bottom: 0;">
                                            @foreach($result['complete'] as $paperName)
                                                <li><small>{{ $paperName }}</small></li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted"><small>None</small></span>
                                    @endif
                                </td>
                                <td>
                                    @if(count($result['incomplete']) > 0)
                                        <ul style="padding-left: 20px; margin-bottom: 0; color: #d9534f;">
                                            @foreach($result['incomplete'] as $paperName)
                                                <li><small>{{ $paperName }}</small></li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <span class="text-muted"><small>None</small></span>
                                    @endif
                                </td>
                            </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">No new papers were found for any researcher.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <h5>Summary Log Details:</h5>
                <ul>
                    <li><strong>Complete Papers:</strong> Data successfully fetched and enriched via OpenAlex (Has Abstract & Keywords).</li>
                    <li><strong>Incomplete Papers:</strong> Inserted successfully but lacks full abstracts or keyword data (OpenAlex couldn't match DOI or lacks data).</li>
                    <li><em>Users with 0 new papers are not shown in this table to save space.</em></li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.3.1.js"></script>
<script src="https://cdn.datatables.net/1.12.0/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.12.0/js/dataTables.bootstrap4.min.js"></script>
<script>
    $(document).ready(function() {
        $('#summary-table').DataTable({
            "paging": true,
            "lengthChange": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "pageLength": 10
        });
    });
</script>
@endsection
