@extends('layouts.layout')
{{-- Inline styles removed — now in style.css (stat-card, count-title, count-text, mixpaper accordion, mobile queries) --}}


@section('content')
<div class="home">
    <!-- Carousel Section -->
    <div class="container d-sm-flex justify-content-center mt-5">
        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="{{asset('img/Banner1.png')}}" class="d-block w-100" alt="...">
                </div>
                <div class="carousel-item">
                    <img src="{{asset('img/Banner2.png')}}" class="d-block w-100" alt="...">
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>

    <!-- Chart Section -->
    <div class="container card-cart d-sm-flex justify-content-center mt-5">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body">
                    <div class="chart" style="height: 350px;">
                        <canvas id="barChart1"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <br>

    <!-- Count Section -->
    <div class="container mt-3 count-section">
        <div class="row text-center">
            <div class="col-6 col-lg-3">
                <div class="count" id='all'></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="count" id='scopus'></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="count" id='wos'></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="count" id='tci'></div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade home-reference-modal" id="myModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reference (APA)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="name"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Publications Section -->
    <div class="container mixpaper pb-10 mt-3">
        <h3>{{ trans('message.publications') }}</h3>
        @foreach($papers as $n => $pe)
        <div class="accordion" id="accordion{{$n}}">
            <div class="accordion-item">
                <h2 class="accordion-header" id="heading{{$n}}">
                    <button class="accordion-button collapsed" type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#collapse{{$n}}"
                        data-year="{{$n}}"
                        aria-expanded="false"
                        aria-controls="collapse{{$n}}">
                        @if (!$loop->last)
                        {{$n}}
                        @else
                        Before {{$n + 1}}
                        @endif
                    </button>
                </h2>
                <div id="collapse{{$n}}"
                    class="accordion-collapse collapse"
                    aria-labelledby="heading{{$n}}"
                    data-bs-parent="#accordion{{$n}}">
                    <div class="accordion-body">
                        <div class="papers-container" id="papers-{{$n}}">
                            <div class="text-center">
                                <div class="spinner-border" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>

<!-- Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>
<!-- เพิ่ม jQuery และ countTo plugin -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-countto/1.2.0/jquery.countTo.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Modal handlers
        const myModal = document.getElementById('myModal');
        const modalInstance = new bootstrap.Modal(myModal);

        myModal.addEventListener('hidden.bs.modal', function() {
            document.getElementById('name').innerHTML = '';
        });

        // Accordion handlers
        const beforeYear = @json(array_key_last($papers));
        const beforeYearLabel = Number(beforeYear) + 1;
        const accordionButtons = document.querySelectorAll('.accordion-button');
        const loadingYears = new Set();

        function renderLoadingState() {
            return `
                <div class="text-center">
                    <div class="spinner-border" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>`;
        }

        function renderPagination(year, pagination) {
            if (!pagination || pagination.last_page <= 1) {
                return '';
            }

            const start = ((pagination.current_page - 1) * pagination.per_page) + 1;
            const end = Math.min(pagination.current_page * pagination.per_page, pagination.total);
            const pages = [];

            pages.push(1);

            for (let page = pagination.current_page - 1; page <= pagination.current_page + 1; page += 1) {
                if (page > 1 && page < pagination.last_page) {
                    pages.push(page);
                }
            }

            if (pagination.last_page > 1) {
                pages.push(pagination.last_page);
            }

            const visiblePages = [...new Set(pages)].sort((a, b) => a - b);
            let buttons = `
                <button class="btn btn-outline-secondary btn-sm pagination-button"
                    data-year="${year}"
                    data-page="${pagination.current_page - 1}"
                    ${pagination.current_page === 1 ? 'disabled' : ''}>
                    Previous
                </button>
            `;

            visiblePages.forEach((page, index) => {
                const previousPage = visiblePages[index - 1];

                if (index > 0 && page - previousPage > 1) {
                    buttons += '<span class="btn btn-sm btn-light disabled">...</span>';
                }

                buttons += `
                    <button class="btn btn-sm ${page === pagination.current_page ? 'btn-primary' : 'btn-outline-primary'} pagination-button"
                        data-year="${year}"
                        data-page="${page}"
                        ${page === pagination.current_page ? 'disabled' : ''}>
                        ${page}
                    </button>
                `;
            });

            buttons += `
                <button class="btn btn-outline-secondary btn-sm pagination-button"
                    data-year="${year}"
                    data-page="${pagination.current_page + 1}"
                    ${pagination.current_page === pagination.last_page ? 'disabled' : ''}>
                    Next
                </button>
            `;

            return `
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mt-3 papers-pagination">
                    <small class="text-muted mb-2 mb-md-0">
                        Showing ${start}-${end} of ${pagination.total} items
                    </small>
                    <div class="btn-group flex-wrap" role="group" aria-label="Paper pagination">
                        ${buttons}
                    </div>
                </div>
            `;
        }

        function renderPapers(year, papers, pagination = null) {
            if (!papers || papers.length === 0) {
                const message = String(year) === String(beforeYear)
                    ? `ไม่พบข้อมูลก่อนปี ${beforeYearLabel}`
                    : `ไม่พบข้อมูลในปี ${year}`;

                return `
                    <div class="alert alert-info text-center">
                        <i class="fa fa-info-circle me-2"></i>
                        ${message}
                    </div>`;
            }

            const startIndex = pagination ? ((pagination.current_page - 1) * pagination.per_page) : 0;
            let html = '';

            papers.forEach((paper, index) => {
                html += `
                    <div class="row mt-2 mb-3 border-bottom">
                        <div class="col-sm-1">
                            <h6>[${startIndex + index + 1}]</h6>
                        </div>
                        <div class="col-sm-11">
                            <p class="hidden">
                                ${paper.paper_name ? `<b>${paper.paper_name}</b>` : '<b>ไม่มีชื่อบทความ</b>'}
                                ${paper.author ? `<span class="paper-author">(${paper.author})</span>` : ''}
                                ${paper.paper_sourcetitle ? paper.paper_sourcetitle : ''}
                                ${paper.paper_volume ? ', ' + paper.paper_volume : ''}
                                ${paper.paper_yearpub ? ', ' + paper.paper_yearpub : ''}.
                                ${paper.paper_url ? `<a href="${paper.paper_url}" target="_blank">[url]</a>` : ''}
                                ${paper.paper_doi ? `<a href="https://doi.org/${paper.paper_doi}" target="_blank">[doi]</a>` : ''}
                                <button style="padding: 0;" class="btn btn-link open_modal" value="${paper.id}">[อ้างอิง]</button>
                            </p>
                        </div>
                    </div>
                `;
            });

            return html + renderPagination(year, pagination);
        }

        async function loadPapers(year, page = 1) {
            const contentDiv = document.getElementById(`papers-${year}`);

            if (!contentDiv || loadingYears.has(year)) {
                return;
            }

            loadingYears.add(year);
            contentDiv.innerHTML = renderLoadingState();

            try {
                const response = await fetch(`/papers_2/${year}?page=${page}`);
                const result = await response.json();
                const papers = Array.isArray(result) ? result : (result.data || []);
                const pagination = Array.isArray(result) ? null : result.pagination;

                contentDiv.innerHTML = renderPapers(year, papers, pagination);
                contentDiv.setAttribute('data-loaded', 'true');
            } catch (error) {
                contentDiv.innerHTML = `
                    <div class="alert alert-danger text-center">
                        <i class="fa fa-exclamation-circle me-2"></i>
                        เกิดข้อผิดพลาดในการโหลดข้อมูล
                    </div>`;
                console.error('Error:', error);
            } finally {
                loadingYears.delete(year);
            }
        }

        accordionButtons.forEach(button => {
            button.addEventListener('click', function() {
                const year = this.getAttribute('data-year');
                const contentDiv = document.getElementById(`papers-${year}`);

                if (!contentDiv.getAttribute('data-loaded')) {
                    loadPapers(year);
                }
            });
        });

        document.addEventListener('click', function(e) {
            const modalButton = e.target.closest('.open_modal');
            if (modalButton) {
                e.preventDefault();
                const tourId = modalButton.value;
                const nameDiv = document.getElementById('name');

                nameDiv.innerHTML = '';

                fetch(`/bib/${tourId}`)
                    .then(response => response.json())
                    .then(data => {
                        nameDiv.innerHTML = data;
                        modalInstance.show();
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        nameDiv.innerHTML = '<div class="alert alert-danger">Error loading citation</div>';
                    });

                return;
            }

            const paginationButton = e.target.closest('.pagination-button');
            if (!paginationButton || paginationButton.disabled) {
                return;
            }

            e.preventDefault();
            loadPapers(
                paginationButton.getAttribute('data-year'),
                paginationButton.getAttribute('data-page')
            );
        });

        // Chart initialization
        var year = <?php echo $year ?? '[]'; ?>;
        var paper_tci = <?php echo $paper_tci ?? '[]'; ?>;
        var paper_scopus = <?php echo $paper_scopus ?? '[]'; ?>;
        var paper_wos = <?php echo $paper_wos ?? '[]'; ?>;

        var areaChartData = {
            labels: year,
            datasets: [{
                    label: 'SCOPUS',
                    backgroundColor: '#3994D6',
                    borderColor: 'rgba(210, 214, 222, 1)',
                    pointRadius: false,
                    pointColor: '#3994D6',
                    pointStrokeColor: '#c1c7d1',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: '#3994D6',
                    data: paper_scopus
                },
                {
                    label: 'TCI',
                    backgroundColor: '#83E4B5',
                    borderColor: 'rgba(255, 255, 255, 0.5)',
                    pointRadius: false,
                    pointColor: '#83E4B5',
                    pointStrokeColor: '#3b8bba',
                    pointHighlightFill: '#fff',
                    pointHighlightStroke: '#83E4B5',
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
                }
            ]
        };

        var barChartCanvas = document.getElementById('barChart1').getContext('2d');
        var barChartData = JSON.parse(JSON.stringify(areaChartData));
        var temp0 = areaChartData.datasets[0];
        var temp1 = areaChartData.datasets[1];
        barChartData.datasets[0] = temp1;
        barChartData.datasets[1] = temp0;

        var barChartOptions = {
            responsive: true,
            maintainAspectRatio: false,
            datasetFill: false,
            scales: {
                yAxes: [{
                    scaleLabel: {
                        display: true,
                        labelString: 'Number'
                    },
                    ticks: {
                        reverse: false,
                        stepSize: 10
                    }
                }],
                xAxes: [{
                    scaleLabel: {
                        display: true,
                        labelString: 'Year'
                    }
                }]
            },
            title: {
                display: true,
                text: 'Report the total number of articles ( 5 years : cumulative)',
                fontSize: 20
            }
        };

        new Chart(barChartCanvas, {
            type: 'bar',
            data: barChartData,
            options: barChartOptions
        });

        // Counter initialization
        var paper_tci_all = <?php echo $paper_tci_numall ?? 0; ?>;
        var paper_scopus_all = <?php echo $paper_scopus_numall ?? 0; ?>;
        var paper_wos_all = <?php echo $paper_wos_numall ?? 0; ?>;
        var sum = paper_wos_all + paper_tci_all + paper_scopus_all;

        function initializeCounter(elementId, value) {
            const element = document.getElementById(elementId);

            // ตรวจสอบว่ามีข้อมูลหรือไม่
            if (value === null || value === undefined || value === 0 || isNaN(value)) {
                element.innerHTML = `
            <i class="fa fa-book fa-2x"></i>
            <h2 class="count-title">ไม่มีข้อมูล</h2>
            <p class="count-text">${elementId.toUpperCase()}</p>`;
            } else {
                element.innerHTML = `
            <i class="fa fa-book fa-2x"></i>
            <h2 class="timer count-title count-number" id="count-${elementId}" data-to="${value}" data-speed="1500">0</h2>
            <p class="count-text">${elementId.toUpperCase()}</p>`;

                // Start counter animation only if we have data
                $(`#count-${elementId}`).countTo({
                    formatter: function(value, options) {
                        return value.toFixed(options.decimals).replace(/\B(?=(?:\d{3})+(?!\d))/g, ',');
                    }
                });
            }
        }

        // Initialize counters after a slight delay
        setTimeout(function() {
            initializeCounter('all', sum > 0 ? sum : null);
            initializeCounter('scopus', paper_scopus_all);
            initializeCounter('wos', paper_wos_all);
            initializeCounter('tci', paper_tci_all);
        }, 500);

        // Counter animation
        jQuery(function($) {
            $('.count-number').data('countToOptions', {
                formatter: function(value, options) {
                    return value.toFixed(options.decimals).replace(/\B(?=(?:\d{3})+(?!\d))/g, ',');
                }
            });

            $('.timer').each(function() {
                var $this = $(this);
                var options = $.extend({}, $this.data('countToOptions') || {});
                $this.countTo(options);
            });
        });
    });
</script>
@endsection
