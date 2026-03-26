@extends('layouts.layout')
{{-- Inline styles removed — moved to style.css (stat-card-sm, custom-tabs-nav, cardprofile, title-pub, btn-export, etc.) --}}

{{-- All styles moved to style.css --}}
<style>
    /* Profile page: count cards in profile use stat-card-sm class */
    .count { display:none; } /* legacy, replaced by stat-card-sm */
    /* Table styling for publication tables */
    thead { background-color: var(--primary, #1075BB); color: white; }
    thead th { font-size: 15px !important; font-weight: 600 !important; padding: 12px 16px !important; white-space: nowrap; border: none !important; }
    tbody tr:nth-child(odd)  { background-color: rgba(190,228,254,0.35) !important; }
    tbody tr:nth-child(even) { background-color: rgb(229,239,247) !important; }
    tbody tr:hover { background-color: rgba(16,117,187,0.06) !important; }
    tbody td { padding: 16px !important; vertical-align: top; line-height: 1.5; border-bottom: 1px solid #E0E8EF; font-size: 14px !important; }
    .paper-link { color: #1075BB; text-decoration: none; font-weight: 600; font-size: 15px; display: block; margin-bottom: 6px; line-height: 1.4; }
    .paper-link:hover { color: #0c5f92; text-decoration: underline; }
    .paper-meta { display: flex; align-items: center; gap: 10px; margin-top: 6px; flex-wrap: wrap; }
    .source-title { color: #666; font-size: 13px; font-style: italic; }
    .citation-count { font-weight: 600; color: #1075BB; text-align: center; font-size: 15px; }
    .badge.bg-info { background-color: rgba(16,117,187,0.1) !important; color: #1075BB !important; padding: 5px 10px; border-radius: 20px; font-weight: 500; font-size: 13px; }
    table { width:100%; border-collapse:separate; border-spacing:0; border-radius:10px; overflow:hidden; margin-bottom:24px; box-shadow:0 0 16px rgba(0,0,0,0.05); }
    @media(max-width:600px) {
        .cardprofile .card .col-md-6 { width:100% !important; }
        tbody td { padding: 10px !important; font-size: 13px !important; }
        thead th { font-size: 13px !important; padding: 10px !important; }
    }
</style>

@section('content')

<div class="container cardprofile">
    <div class="card">
        <div class="row g-0">
            <div class="col-md-2">
                <img class="card-image" src="{{ $res->profile_picture_url ?? ($res->picture ?? asset('img/default-profile.png')) }}" alt="">
            </div>
            <div class="col-md-6" style="width:40%">
                <div class="card-body" style="width:auto">
                    <h6 class="card-text"><b>{{$res->position_th}} {{$res->fname_th}} {{$res->lname_th}}</b></h6>
                    @if($res->doctoral_degree == 'Ph.D.')
                    <h6 class="card-text"><b>{{$res->fname_en}} {{$res->lname_en}}, {{$res->doctoral_degree}} </b>
                        @else
                        <h6 class="card-text"><b>{{$res->fname_en}} {{$res->lname_en}}</b>
                            @endif</h6>
                        <h6 class="card-text1"><b>{{$res->academic_ranks_en}}</b></h6>
                        <!-- <h6 class="card-text1">Department of {{$res->program->program_name_en}}</h6> -->
                        <!-- <h6 class="card-text1">College of Computing</h6>
                    <h6 class="card-text1">Khon Kaen University</h6> -->
                        @if(!empty($res->email))
                        <h6 class="card-text1">E-mail: {{$res->email}}</h6>
                        @endif
                        @if(!empty($res->affiliation))
                        <h6 class="card-text1">Affiliation: {{$res->affiliation}}</h6>
                        @endif
                        @if(!empty($res->orcid))
                        <h6 class="card-text1">ORCID: {{$res->orcid}}</h6>
                        @endif
                        <h6 class="card-title">{{ trans('message.education') }}</h6>
                        @foreach( $res->education as $edu)
                        <h6 class="card-text2 col-sm-10" style="line-height: 1.6;"> {{$edu->year}} {{$edu->qua_name}} <br> {{$edu->uname}}</h6>
                        @endforeach
                        <!-- <button type="button" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#exampleModal">
                            {{ trans('message.expertise') }}
                        </button> -->
                        <!-- <h6 class="card-title">Metrics overview</h6>
                    <h6 class="card-text2" id="citation">Citation count</h6>
                    <h6 class="card-text2" id="doc_count">Document count</h6>
                    <h6 class="card-text2" id="cite_count">Cited By count</h6>
                    <h6 class="card-text2" id="h-index">H-index </h6> -->

                </div>
            </div>

            <div class="col-md-4">
                <h6 class="title-pub">{{ trans('message.publications2') }}</h6>
                <div class="col-xs-12 text-center bt">
                    <div class="clearfix"></div>
                    <div class="row text-center gx-1">
                        <div class="col">
                            <div class="count" id='all'>
                            </div>
                        </div>
                        <div class="col">
                            <div class="count" id='scopus_sum'>
                            </div>
                        </div>
                        <div class="col">
                            <div class="count" id='wos_sum'>
                            </div>
                        </div>
                        <div class="col">
                            <div class="count" id='tci_sum'>
                            </div>
                        </div>
                    </div>
                    <div class="chart">
                        <canvas id="barChart"></canvas>
                    </div>
                </div>
            </div>



        </div>
    </div>
    <!-- <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">ความเชี่ยวชาญ</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                @foreach($res->expertise as $exper)
                                <p class="card-text"> {{$exper->expert_name}}</p>
                                @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div> -->
    <br>

    {{-- Mobile-scrollable tabs navigation --}}
    <div class="custom-tabs-wrap">
        <nav class="custom-tabs-nav" id="myTab" role="tablist">
            <button class="custom-tab-btn active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Summary</button>
            <button class="custom-tab-btn" id="scopus-tab" data-bs-toggle="tab" data-bs-target="#scopus" type="button" role="tab" aria-controls="scopus" aria-selected="false">SCOPUS</button>
            <button class="custom-tab-btn" id="wos-tab" data-bs-toggle="tab" data-bs-target="#wos" type="button" role="tab" aria-controls="wos" aria-selected="false">WEB OF SCIENCE</button>
            <button class="custom-tab-btn" id="tci-tab" data-bs-toggle="tab" data-bs-target="#tci" type="button" role="tab" aria-controls="tci" aria-selected="false">TCI</button>
            <button class="custom-tab-btn" id="book-tab" data-bs-toggle="tab" data-bs-target="#book" type="button" role="tab" aria-controls="book" aria-selected="false">หนังสือ</button>
            <button class="custom-tab-btn" id="patent-tab" data-bs-toggle="tab" data-bs-target="#patent" type="button" role="tab" aria-controls="patent" aria-selected="false">ผลงานวิชาการด้านอื่นๆ</button>
        </nav>
        @if($showExport)
        <a class="btn-export" href="{{ route('excel', ['id' => $res->id]) }}" target="_blank" aria-label="Export to Excel">
            <img src="https://cdn-icons-png.flaticon.com/512/3405/3405255.png" alt="Export Icon" class="icon-export" />
        </a>
        @endif
    </div>



    <br>
    <div class="tab-content" id="myTabContent">

        <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab">
            <div class="tab-content" style="padding-bottom: 20px;">



            </div>
            <table id="example1" class="table table-striped" style="width:100%">
                <thead>
                    <!-- <tr>
                        <th><a href="{{ route('excel', ['id' => $res->id]) }}" target="_blank">#Export</a></td>
                    </tr> -->
                    <tr>
                        <!-- <th>No.</th> -->
                        <th>Year</th>
                        <th>Paper Name</th>
                        <!-- <th>Author</th>
                        <th>Document Type</th>
                        <th>Page</th>
                        <th>Journals/Transactions</th> -->
                        <th>Ciations</th>
                        <!-- <th>Doi</th>
                        <th>Source</th> -->
                    </tr>
                </thead>

                <tbody>
                </tbody>
            </table>

        </div>
        <div class="tab-pane fade" id="scopus" role="tabpanel" aria-labelledby="scopus-tab">

            <table id="example2" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Paper Name</th>
                        <th>Ciations</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>


        </div>
        <div class="tab-pane fade" id="wos" role="tabpanel" aria-labelledby="wos-tab">

            <table id="example3" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Paper Name</th>
                        <th>Ciations</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>


        </div>

        <div class="tab-pane fade" id="tci" role="tabpanel" aria-labelledby="tci-tab">
            <table id="example4" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th>Year</th>
                        <th>Paper Name</th>
                        <th>Ciations</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
        <div class="tab-pane fade" id="book" role="tabpanel" aria-labelledby="book-tab">
            <table id="example5" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th scope="col">Number</th>
                        <th scope="col">Year</th>
                        <th scope="col">Name</th>
                        <th scope="col">Author</th>
                        <th scope="col">สถานที่พิมพ์</th>
                        <th scope="col">Page</th>

                    </tr>
                </thead>

                <tbody>
                </tbody>
            </table>
        </div>

        <div class="tab-pane fade" id="patent" role="tabpanel" aria-labelledby="patent-tab">
            <table id="example6" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th scope="col">Number</th>
                        <th scope="col">Name</th>
                        <th scope="col">Author</th>
                        <th scope="col">ประเภท</th>
                        <th scope="col">หมายเลขทะเบียน</th>
                        <th scope="col">วันที่จดทะเบียน</th>

                    </tr>
                </thead>

                <tbody>
                </tbody>
            </table>
        </div>

    </div>
</div>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/dataTables.bootstrap4.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>

<script>
    $(document).ready(function() {
        // ค่าตั้งต้นของ DataTable
        const tableConfig = {
            responsive: true,
            order: [
                [0, 'desc']
            ], // เรียงลำดับปีจากใหม่ไปเก่า
            language: {
                search: "ค้นหาข้อมูล:",
                lengthMenu: "แสดง _MENU_ รายการต่อหน้า",
                info: "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                paginate: {
                    first: "หน้าแรก",
                    last: "หน้าสุดท้าย",
                    next: "ถัดไป",
                    previous: "ก่อนหน้า"
                }
            },
            searching: false, // ปิดการค้นหาในตาราง
            columnDefs: [{
                    targets: '_all',
                    searchable: true
                } // เปิดให้ค้นหาได้ทุกคอลัมน์
            ]
        };

        // สร้าง DataTable ให้กับทุกตาราง
        const profileId = '{{ $profileId }}';
        const profileType = '{{ $profileType }}';
        const userIdQuery = {!! isset($paperDetailUserId) ? "'?user_id=' + " . $paperDetailUserId : "''" !!};

        const paperColumns = [
            { data: 'paper_yearpub', width: '10%' },
            {
                data: null,
                width: '80%',
                render: function(data, type, row) {
                    let paperName = row.paper_name ? row.paper_name.replace(/<inf>/g, 'sub').replace(/<\/inf>/g, '/sub') : '';
                    let url = '/paper/' + row.id + '/detail' + userIdQuery;
                    return `
                        <div class="paper-content">
                            <a href="${url}" class="paper-link">${paperName}</a>
                            <div class="paper-meta">
                                <span class="badge bg-info">${row.paper_type || ''}</span>
                                <span class="source-title">${row.paper_sourcetitle || ''}</span>
                            </div>
                        </div>
                    `;
                }
            },
            { data: 'paper_citation', className: 'citation-count', width: '10%' }
        ];

        let t1 = $('#example1').DataTable({
            ...tableConfig,
            ajax: { url: `/profile/${profileId}/papers?type=${profileType}&source=all`, dataSrc: '' },
            columns: paperColumns
        });

        let t2 = $('#example2').DataTable({
            ...tableConfig,
            ajax: { url: `/profile/${profileId}/papers?type=${profileType}&source=scopus`, dataSrc: '' },
            columns: paperColumns
        });

        let t3 = $('#example3').DataTable({
            ...tableConfig,
            ajax: { url: `/profile/${profileId}/papers?type=${profileType}&source=wos`, dataSrc: '' },
            columns: paperColumns
        });

        let t4 = $('#example4').DataTable({
            ...tableConfig,
            ajax: { url: `/profile/${profileId}/papers?type=${profileType}&source=tci`, dataSrc: '' },
            columns: paperColumns
        });

        let t5 = $('#example5').DataTable({
            ...tableConfig,
            order: [[1, 'desc']],
            ajax: { url: `/profile/${profileId}/academic-works?type=${profileType}&work_type=book`, dataSrc: '' },
            columns: [
                { data: null, render: function (data, type, row, meta) { return meta.row + 1; } },
                { data: 'ac_year', render: function(data) { return data ? parseInt(data.substring(0,4)) + 543 : ''; } },
                { data: 'ac_name' },
                {
                    data: null,
                    render: function(data, type, row) {
                        let authors = [];
                        if (row.author) {
                            row.author.forEach(a => authors.push(`<span><a>${a.author_fname} ${a.author_lname}</a></span>`));
                        }
                        if (row.user) {
                            row.user.forEach(u => authors.push(`<span><a>${u.fname_en} ${u.lname_en}</a></span>`));
                        }
                        return authors.join(' ');
                    }
                },
                { data: 'ac_sourcetitle' },
                { data: 'ac_page' }
            ]
        });

        let t6 = $('#example6').DataTable({
            ...tableConfig,
            ajax: { url: `/profile/${profileId}/academic-works?type=${profileType}&work_type=other`, dataSrc: '' },
            columns: [
                { data: null, render: function (data, type, row, meta) { return meta.row + 1; } },
                { data: 'ac_name' },
                {
                    data: null,
                    render: function(data, type, row) {
                        let authors = [];
                        if (row.author) {
                            row.author.forEach(a => authors.push(`<span><a>${a.author_fname} ${a.author_lname}</a></span>`));
                        }
                        if (row.user) {
                            // Note: We bypass decrypting here since it relies on PHP, but it just displays names cleanly.
                            row.user.forEach(u => authors.push(`<span><teacher>${u.fname_en} ${u.lname_en}</teacher></span>`));
                        }
                        return authors.join(' ');
                    }
                },
                { data: 'ac_type' },
                { data: 'ac_refnumber' },
                { data: 'ac_year' }
            ]
        });

        // Store references for the search
        const table1 = t1, table2 = t2, table3 = t3, table4 = t4, table5 = t5, table6 = t6;

        // เพิ่ม input ค้นหากลางสำหรับทุกตาราง
        const searchBox = $('<div class="mb-3"><input type="text" id="globalSearch" class="form-control" placeholder="🔍 ค้นหาชื่อ, ปี, หรือรายละเอียด..." style="width: 100%; padding: 8px; border: 1px solid #1075BB; border-radius: 4px;"></div>');
        $(".nav-container").after(searchBox);

        // ฟังก์ชันค้นหาทั่วไป
        $('#globalSearch').on('keyup', function() {
            let value = $(this).val();
            table1.search(value).draw();
            table2.search(value).draw();
            table3.search(value).draw();
            table4.search(value).draw();
            table5.search(value).draw();
            table6.search(value).draw();
        });

        // ให้ DataTable รีเฟรชเมื่อเปลี่ยนแท็บ
        $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(event) {
            const tabID = $(event.target).attr('data-bs-target');
            const tableMap = {
                '#home': table1,
                '#scopus': table2,
                '#wos': table3,
                '#tci': table4,
                '#book': table5,
                '#patent': table6
            };

            if (tableMap[tabID]) {
                tableMap[tabID].columns.adjust().draw();
            }
        });
    });
</script>

<script>
    var year = <?php echo $year; ?>;
    var paper_tci = <?php echo $paper_tci; ?>;
    var paper_scopus = <?php echo $paper_scopus; ?>;
    var paper_wos = <?php echo $paper_wos; ?>;
    var areaChartData = {

        labels: year,

        datasets: [{
                label: 'SCOPUS',
                backgroundColor: '#83E4B5',
                borderColor: 'rgba(255, 255, 255, 0.5)',
                pointRadius: false,
                pointColor: '#83E4B5',
                pointStrokeColor: '#3b8bba',
                pointHighlightFill: '#fff',
                pointHighlightStroke: '#83E4B5',
                data: paper_scopus
            },
            {
                label: 'TCI',
                backgroundColor: '#3994D6',
                borderColor: 'rgba(210, 214, 222, 1)',
                pointRadius: false,
                pointColor: '#3994D6',
                pointStrokeColor: '#c1c7d1',
                pointHighlightFill: '#fff',
                pointHighlightStroke: '#3994D6',
                data: paper_tci
            },
            {
                label: 'WOS',
                backgroundColor: '#FCC29A',
                borderColor: 'rgba(0, 0, 255, 1)',
                pointRadius: false,
                pointColor: '#FCC29A',
                pointStrokeColor: '#c1c7d1',
                pointHighlightFill: '#fff',
                pointHighlightStroke: '#FCC29A',
                data: paper_wos
            },
        ]
    }



    //-------------
    //- BAR CHART -
    //-------------
    var barChartCanvas = $('#barChart').get(0).getContext('2d')
    var barChartData = $.extend(true, {}, areaChartData)
    var temp0 = areaChartData.datasets[0]
    var temp1 = areaChartData.datasets[1]
    barChartData.datasets[0] = temp1
    barChartData.datasets[1] = temp0

    var barChartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        datasetFill: false,
        scales: {
            yAxes: [{
                ticks: {
                    stepSize: 1
                }
            }]
        }

    }

    new Chart(barChartCanvas, {
        type: 'bar',
        data: barChartData,
        options: barChartOptions
    })
</script>

<script type="text/javascript">
    function myDisplayer(some) {

        document.getElementById("citation").innerHTML = "Citation count : " + some['h-index'];
        document.getElementById("doc_count").innerHTML = "Document count : " + some['coredata']['citation-count'];
        document.getElementById("cite_count").innerHTML = "Cited By count : " + some['coredata']['cited-by-count'];
        document.getElementById("h-index").innerHTML = "H-index : " + some['h-index'];

    }
    async function myFunction() {
        var res = <?php echo $res; ?>;
        //var fname = res.fname_en;
        //var fname = res.fname_en.substr(0, 1);
        //console.log(fname);
        //const response = await fetch('https://api.elsevier.com/content/search/scopus?query=AUTHOR-NAME('+ res.lname_en +','+fname+')%20&apikey=6ab3c2a01c29f0e36b00c8fa1d013f83&httpAccept=application%2Fjson');
        const response = await fetch('https://api.elsevier.com/content/search/author?query=authlast(' + res.lname_en +
            ')%20and%20authfirst(' + res.fname_en +
            ')%20&apiKey=6ab3c2a01c29f0e36b00c8fa1d013f83&httpAccept=application%2Fjson');
        //var a = got["search-results"];
        const got = await response.json();
        aid = got["search-results"]["entry"][0]['dc:identifier'];
        aid = aid.split(":");
        aid = aid[1];
        const resultC = await fetch('https://api.elsevier.com/content/author?author_id=' + aid +
            '&view=metrics&apiKey=6ab3c2a01c29f0e36b00c8fa1d013f83&httpAccept=application%2Fjson');
        const data = await resultC.json();
        auth = data['author-retrieval-response'][0];
        //data = data['h-index'];

        return auth

    }
    myFunction().then(
        function(value) {
            myDisplayer(value);
        },
        function(error) {
            myDisplayer(error);
        }
    );
</script>
</div>
<script>
    var paper_tci_s = <?php echo $paper_tci_s; ?>;
    var paper_scopus_s = <?php echo $paper_scopus_s; ?>;
    var paper_wos_s = <?php echo $paper_wos_s; ?>;
    var paper_book_s = <?php echo $paper_book_s; ?>;
    var paper_patent_s = <?php echo $paper_patent_s; ?>;
    //console.log(paper_book_s);
    let sumtci = 0;
    let sumsco = 0;
    let sumwos = 0;
    let sumbook = 0;
    let sumpatent = 0;
    (function($) {
        for (let i = 0; i < paper_scopus_s.length; i++) {
            sumsco += paper_scopus_s[i];
        }
        for (let i = 0; i < paper_tci_s.length; i++) {
            sumtci += paper_tci_s[i];
        }
        for (let i = 0; i < paper_wos_s.length; i++) {
            sumwos += paper_wos_s[i];
        }
        for (let i = 0; i < paper_book_s.length; i++) {
            sumbook += paper_book_s[i];
        }
        for (let i = 0; i < paper_patent_s.length; i++) {
            sumpatent += paper_patent_s[i];
        }
        let sum = sumsco + sumtci + sumwos + sumbook + sumpatent;

        //$("#scopus").append('data-to="100"');
        document.getElementById("all").innerHTML += `
                <h2 class="timer count-title count-number" data-to="${sum}" data-speed="1500"></h2>
                <p class="count-text ">SUMMARY</p>`

        document.getElementById("scopus_sum").innerHTML += `
                <h2 class="timer count-title count-number" data-to="${sumsco}" data-speed="1500"></h2>
                <p class="count-text">SCOPUS</p>`

        document.getElementById("wos_sum").innerHTML += `
                <h2 class="timer count-title count-number" data-to="${sumwos}" data-speed="1500"></h2>
                <p class="count-text ">WOS</p>`

        document.getElementById("tci_sum").innerHTML += `
                <h2 class="timer count-title count-number" data-to="${sumtci}" data-speed="1500"></h2>
                <p class="count-text ">TCI</p>`


        //document.getElementById("scopus").appendChild('data-to="100"');
        $.fn.countTo = function(options) {
            options = options || {};

            return $(this).each(function() {
                // set options for current element
                var settings = $.extend({}, $.fn.countTo.defaults, {
                    from: $(this).data('from'),
                    to: $(this).data('to'),
                    speed: $(this).data('speed'),
                    refreshInterval: $(this).data('refresh-interval'),
                    decimals: $(this).data('decimals')
                }, options);

                // how many times to update the value, and how much to increment the value on each update
                var loops = Math.ceil(settings.speed / settings.refreshInterval),
                    increment = (settings.to - settings.from) / loops;

                // references & variables that will change with each update
                var self = this,
                    $self = $(this),
                    loopCount = 0,
                    value = settings.from,
                    data = $self.data('countTo') || {};

                $self.data('countTo', data);

                // if an existing interval can be found, clear it first
                if (data.interval) {
                    clearInterval(data.interval);
                }
                data.interval = setInterval(updateTimer, settings.refreshInterval);

                // initialize the element with the starting value
                render(value);

                function updateTimer() {
                    value += increment;
                    loopCount++;

                    render(value);

                    if (typeof(settings.onUpdate) == 'function') {
                        settings.onUpdate.call(self, value);
                    }

                    if (loopCount >= loops) {
                        // remove the interval
                        $self.removeData('countTo');
                        clearInterval(data.interval);
                        value = settings.to;

                        if (typeof(settings.onComplete) == 'function') {
                            settings.onComplete.call(self, value);
                        }
                    }
                }

                function render(value) {
                    var formattedValue = settings.formatter.call(self, value, settings);
                    $self.html(formattedValue);
                }
            });
        };

        $.fn.countTo.defaults = {
            from: 0, // the number the element should start at
            to: 0, // the number the element should end at
            speed: 1000, // how long it should take to count between the target numbers
            refreshInterval: 100, // how often the element should be updated
            decimals: 0, // the number of decimal places to show
            formatter: formatter, // handler for formatting the value before rendering
            onUpdate: null, // callback method for every time the element is updated
            onComplete: null // callback method for when the element finishes updating
        };

        function formatter(value, settings) {
            return value.toFixed(settings.decimals);
        }
    }(jQuery));

    jQuery(function($) {
        // custom formatting example
        $('.count-number').data('countToOptions', {
            formatter: function(value, options) {
                return value.toFixed(options.decimals).replace(/\B(?=(?:\d{3})+(?!\d))/g, ',');
            }
        });

        // start all the timers
        $('.timer').each(count);

        function count(options) {
            var $this = $(this);
            options = $.extend({}, options || {}, $this.data('countToOptions') || {});
            $this.countTo(options);
        }
    });
</script>
<!-- <script>
    // get the p element
    $(document).ready(function() {
    const a = document.getElementById('authtd');
    console.log(a.text)
    const myArray =  a.text.toString().split(" ");
    console.log(myArray)
    document.getElementById("authtd").innerHTML = "name :"+ myArray;

});
</script> -->
@endsection
