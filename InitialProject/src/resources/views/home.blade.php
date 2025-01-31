@extends('layouts.layout')
<style>
    .count {
        background-color: #f5f5f5;
        padding: 20px 0;
        border-radius: 5px;
    }

    .count-title {
        font-size: 40px;
        font-weight: normal;
        margin-top: 10px;
        margin-bottom: 0;
        text-align: center;
    }

    .count-text {
        font-size: 15px;
        font-weight: normal;
        margin-top: 10px;
        margin-bottom: 0;
        text-align: center;
    }

    .fa-2x {
        margin: 0 auto;
        float: none;
        display: table;
        color: #4ad1e5;
    }
</style>

@section('content')
<div class="container home">
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
    <div class="container mt-3">
        <div class="row text-center">
            <div class="col">
                <div class="count" id='all'></div>
            </div>
            <div class="col">
                <div class="count" id='scopus'></div>
            </div>
            <div class="col">
                <div class="count" id='wos'></div>
            </div>
            <div class="col">
                <div class="count" id='tci'></div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="myModal" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
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
                            Before {{$n}}
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Modal handlers
    const myModal = document.getElementById('myModal');
    const modalInstance = new bootstrap.Modal(myModal);

    myModal.addEventListener('hidden.bs.modal', function() {
        document.getElementById('name').innerHTML = '';
    });

    // Accordion handlers
    const accordionButtons = document.querySelectorAll('.accordion-button');
    
    accordionButtons.forEach(button => {
        let isLoading = false;
        
        button.addEventListener('click', async function() {
            const year = this.getAttribute('data-year');
            const contentDiv = document.getElementById(`papers-${year}`);
            
            if (isLoading) return;
            
            if (!contentDiv.getAttribute('data-loaded')) {
                isLoading = true;
                try {
                    const response = await fetch(`/papers/${year}`);
                    const data = await response.json();
                    
                    let html = '';
                    data.forEach((paper, index) => {
                        html += `
                            <div class="row mt-2 mb-3 border-bottom">
                                <div class="col-sm-1">
                                    <h6>[${index + 1}]</h6>
                                </div>
                                <div class="col-sm-11">
                                    <p class="hidden">
                                        <b>${paper.paper_name}</b> (
                                        <link>${paper.author}</link>), ${paper.paper_sourcetitle}, ${paper.paper_volume},
                                        ${paper.paper_yearpub}.
                                        <a href="${paper.paper_url}" target="_blank">[url]</a>
                                        <a href="https://doi.org/${paper.paper_doi}" target="_blank">[doi]</a>
                                        <button style="padding: 0;" class="btn btn-link open_modal" value="${paper.id}">[อ้างอิง]</button>
                                    </p>
                                </div>
                            </div>
                        `;
                    });
                    
                    contentDiv.innerHTML = html;
                    contentDiv.setAttribute('data-loaded', 'true');
                    initializeModalHandlers();
                    
                } catch (error) {
                    contentDiv.innerHTML = '<div class="alert alert-danger">Error loading papers</div>';
                    console.error('Error:', error);
                } finally {
                    isLoading = false;
                }
            }
        });
    });

    function initializeModalHandlers() {
        document.querySelectorAll('.open_modal').forEach(button => {
            button.addEventListener('click', function(e) {
                e.preventDefault();
                const tourId = this.value;
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
            });
        });
    }

    // Chart initialization
    var year = <?php echo $year; ?>;
    var paper_tci = <?php echo $paper_tci; ?>;
    var paper_scopus = <?php echo $paper_scopus; ?>;
    var paper_wos = <?php echo $paper_wos; ?>;
    
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
        }]
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
    var paper_tci_all = <?php echo $paper_tci_numall; ?>;
    var paper_scopus_all = <?php echo $paper_scopus_numall; ?>;
    var paper_wos_all = <?php echo $paper_wos_numall; ?>;
    var sum = paper_wos_all + paper_tci_all + paper_scopus_all;

    function initializeCounter(elementId, value) {
        document.getElementById(elementId).innerHTML = `
            <i class="count-icon fa fa-book fa-2x"></i>
            <h2 class="timer count-title count-number" data-to="${value}" data-speed="1500"></h2>
            <p class="count-text ">${elementId.toUpperCase()}</p>`;
    }

    initializeCounter('all', sum);
    initializeCounter('scopus', paper_scopus_all);
    initializeCounter('wos', paper_wos_all);
    initializeCounter('tci', paper_tci_all);

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