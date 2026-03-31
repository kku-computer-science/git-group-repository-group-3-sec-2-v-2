@extends('layouts.layout')
@section('content')

@include('partials.page-header', ['title' => 'รายงานสถิติ'])

<div class="container pb-5">

    {{-- Chart 1: Total papers 5 years --}}
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body">
            <h5 class="card-title" style="color:#1075BB;font-family:'Kanit',sans-serif;padding:8px 0;">
                สถิติจำนวนบทความทั้งหมด 5 ปี
            </h5>
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="report-chart-wrap">
                        <canvas id="barChart1"></canvas>
                    </div>
                </div>
                <div class="col-md-6 table-responsive">
                    <table class="table table-bordered table-sm" id="myTable">
                        <thead><tr></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart 2: Citation count --}}
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <h5 class="card-title" style="color:#1075BB;font-family:'Kanit',sans-serif;padding:8px 0;">
                สถิติจำนวนบทความที่ได้รับการอ้างอิง
            </h5>
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="report-chart-wrap">
                        <canvas id="barChart2"></canvas>
                    </div>
                </div>
                <div class="col-md-6 table-responsive">
                    <table class="table table-bordered table-sm" id="myTable2">
                        <thead><tr></tr></thead>
                        <tbody><tr></tr></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
<script>
(function() {
    var year         = <?php echo $year ?? '[]'; ?>;
    var paper_tci    = <?php echo $paper_tci ?? '[]'; ?>;
    var paper_scopus = <?php echo $paper_scopus ?? '[]'; ?>;
    var paper_wos    = <?php echo $paper_wos ?? '[]'; ?>;
    var paper_tci_cit    = <?php echo $paper_tci_cit ?? '[]'; ?>;
    var paper_scopus_cit = <?php echo $paper_scopus_cit ?? '[]'; ?>;
    var paper_wos_cit    = <?php echo $paper_wos_cit ?? '[]'; ?>;

    var CHART_COLORS = {
        scopus: '#3994D6',
        tci:    '#83E4B5',
        wos:    '#FCC29A'
    };

    /** Build chart data object */
    function makeChartData(scopus, tci, wos, years) {
        return {
            labels: years,
            datasets: [
                { label: 'SCOPUS', backgroundColor: CHART_COLORS.scopus, pointRadius: false, data: scopus },
                { label: 'TCI',    backgroundColor: CHART_COLORS.tci,    pointRadius: false, data: tci    },
                { label: 'WOS',    backgroundColor: CHART_COLORS.wos,    pointRadius: false, data: wos    }
            ]
        };
    }

    /** Render a bar chart */
    function renderBarChart(canvasId, data) {
        var ctx = document.getElementById(canvasId).getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: data,
            options: { responsive: true, maintainAspectRatio: false, datasetFill: false }
        });
    }

    /** Generate table from array of objects */
    function generateTableHead(table, keys) {
        var thead = table.tHead || table.createTHead();
        thead.innerHTML = '';
        var row = thead.insertRow();
        keys.forEach(function(k) { var th = document.createElement('th'); th.textContent = k; row.appendChild(th); });
    }

    function generateTable(table, data) {
        var tbody = table.tBodies[0] || table.createTBody();
        tbody.innerHTML = '';
        data.forEach(function(row) {
            var tr = tbody.insertRow();
            Object.values(row).forEach(function(v) { var td = tr.insertCell(); td.textContent = v; });
        });
    }

    /** Convert parallel arrays to array-of-objects for table rendering */
    function arraysToObjects(years, datasets) {
        var headers = ['source'].concat(datasets.map(function(d) { return d.label; }));
        return years.map(function(y, i) {
            var obj = { source: y };
            datasets.forEach(function(d) { obj[d.label] = d.data[i] ?? 0; });
            return obj;
        });
    }

    // Chart 1 — paper count
    var chart1Data = makeChartData(paper_scopus, paper_tci, paper_wos, year);
    renderBarChart('barChart1', chart1Data);
    var table1 = document.getElementById('myTable');
    var rows1   = arraysToObjects(year, chart1Data.datasets);
    generateTableHead(table1, Object.keys(rows1[0] || {}));
    generateTable(table1, rows1);

    // Chart 2 — citation count
    var chart2Data = makeChartData(paper_scopus_cit, paper_tci_cit, paper_wos_cit, year);
    renderBarChart('barChart2', chart2Data);
    var table2 = document.getElementById('myTable2');
    var rows2   = arraysToObjects(year, chart2Data.datasets);
    generateTableHead(table2, Object.keys(rows2[0] || {}));
    generateTable(table2, rows2);
})();
</script>
@endsection