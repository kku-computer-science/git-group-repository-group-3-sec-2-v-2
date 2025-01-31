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
    <div class="container d-sm-flex justify-content-center mt-5">
        <div id="carouselExampleIndicators" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <!-- <button type="button" data-bs-target="#carouselExampleIndicators" data-bs-slide-to="2"
                aria-label="Slide 3"></button> -->
            </div>
            <div class="carousel-inner">
                <div class="carousel-item active">
                    <img src="{{asset('img/Banner1.png')}}" class="d-block w-100" alt="...">
                </div>
                <div class="carousel-item">
                    <img src="{{asset('img/Banner2.png')}}" class="d-block w-100" alt="...">
                </div>
                <!-- <div class="carousel-item">
                <img src="..." class="d-block w-100" alt="...">
            </div> -->
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


    <!-- Modal -->



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

    <div class="container mt-3">

        <div class="row text-center">
            <div class="col">
                <div class="count" id='all'>

                </div>
            </div>
            <div class="col">
                <div class="count" id='scopus'>

                </div>
            </div>
            <div class="col">
                <div class="count" id='wos'>

                </div>
            </div>
            <div class="col">
                <div class="count" id='tci'>

                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="myModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Reference (APA)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="name">
                    <!-- <p>Modal body text goes here.</p> -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <!-- <button type="button" class="btn btn-primary">Save changes</button> -->
                </div>
            </div>
        </div>
    </div>




<!-- Modified accordion section in home.blade.php -->
<div class="container mixpaper pb-10 mt-3">
    <h3>{{ trans('message.publications') }}</h3>
    @foreach($papers as $n => $pe)
    <div class="accordion" id="accordionExample">
        <div class="accordion-item">
            <h2 class="accordion-header" id="heading{{$n}}">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
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
            <div id="collapse{{$n}}" class="accordion-collapse collapse" 
                 aria-labelledby="heading{{$n}}" 
                 data-bs-parent="#accordionExample">
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.3/Chart.min.js"></script>

<!-- ส่วน HTML ยังคงเหมือนเดิม แต่ปรับปรุง script -->
<!-- ส่วน HTML ยังคงเหมือนเดิม แต่ปรับปรุง script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const accordionButtons = document.querySelectorAll('.accordion-button');
    
    accordionButtons.forEach(button => {
        let isLoading = false;
        
        button.addEventListener('click', async function() {
            const year = this.getAttribute('data-year');
            const contentDiv = document.getElementById(`papers-${year}`);
            const collapseElement = document.getElementById(`collapse${year}`);
            const isExpanded = button.classList.contains('collapsed');
            
            // ถ้ากำลังโหลดอยู่ ไม่ต้องทำอะไร
            if (isLoading) return;
            
            // ถ้ายังไม่เคยโหลดข้อมูล
            if (!contentDiv.getAttribute('data-loaded')) {
                isLoading = true;
                
                try {
                    // เริ่มโหลดข้อมูล
                    const response = await fetch(`/papers/${year}`);
                    const data = await response.json();
                    
                    // สร้าง HTML
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
                    
                    // อัพเดท DOM และเก็บข้อมูลไว้
                    contentDiv.innerHTML = html;
                    contentDiv.setAttribute('data-loaded', 'true');
                    
                    // อัพเดทปุ่ม modal
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
    
    // ใช้ Event Listener ของ Bootstrap Collapse
    document.querySelectorAll('.accordion-collapse').forEach(collapse => {
        collapse.addEventListener('show.bs.collapse', function() {
            const button = document.querySelector(`[data-bs-target="#${this.id}"]`);
            button.classList.remove('collapsed');
            button.setAttribute('aria-expanded', 'true');
        });
        
        collapse.addEventListener('hide.bs.collapse', function() {
            const button = document.querySelector(`[data-bs-target="#${this.id}"]`);
            button.classList.add('collapsed');
            button.setAttribute('aria-expanded', 'false');
        });
    });
    
    function initializeModalHandlers() {
        document.querySelectorAll('.open_modal').forEach(button => {
            button.addEventListener('click', function() {
                const tourId = this.value;
                fetch(`/bib/${tourId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.querySelectorAll('.bibtex-biblio').forEach(el => el.remove());
                        document.getElementById('name').innerHTML += data;
                        new bootstrap.Modal(document.getElementById('myModal')).show();
                    })
                    .catch(error => {
                        console.error('Error loading bibtex:', error);
                    });
            });
        });
    }
});
</script>

<script>
    $(document).ready(function() {
        $(".moreBox").slice(0, 1).show();
        if ($(".blogBox:hidden").length != 0) {
            $("#loadMore").show();
        }
        $("#loadMore").on('click', function(e) {
            e.preventDefault();
            $(".moreBox:hidden").slice(0, 1).slideDown();
            if ($(".moreBox:hidden").length == 0) {
                $("#loadMore").fadeOut('slow');
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
            },
        ]
    }



    //-------------
    //- BAR CHART -
    //-------------
    var barChartCanvas = $('#barChart1').get(0).getContext('2d')
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
                formatter: function() {
                    return Math.abs(this.value);
                },
                scaleLabel: {
                    display: true,
                    labelString: 'Number',

                },
                ticks: {
                    reverse: false,
                    stepSize: 10
                },
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


    }

    new Chart(barChartCanvas, {
        type: 'bar',
        data: barChartData,
        options: barChartOptions
    })
</script>
<script>
    var paper_tci = <?php echo $paper_tci_numall; ?>;
    var paper_scopus = <?php echo $paper_scopus_numall; ?>;
    var paper_wos = <?php echo $paper_wos_numall; ?>;
    //console.log(paper_scopus)
    let sumtci = paper_tci;
    let sumsco = paper_scopus;
    let sumwos = paper_wos;
    (function($) {
        
        let sum = paper_wos + paper_tci + paper_scopus;
        //console.log(sum);
        //$("#scopus").append('data-to="100"');
        document.getElementById("all").innerHTML += `
                <i class="count-icon fa fa-book fa-2x"></i>
                <h2 class="timer count-title count-number" data-to="${sum}" data-speed="1500"></h2>
                <p class="count-text ">SUMMARY</p>`
        document.getElementById("scopus").innerHTML += `
                <i class="count-icon fa fa-book fa-2x"></i>
                <h2 class="timer count-title count-number" data-to="${sumsco}" data-speed="1500"></h2>
                <p class="count-text ">SCOPUS</p>`
        document.getElementById("wos").innerHTML += `
                <i class="count-icon fa fa-book fa-2x"></i>
                <h2 class="timer count-title count-number" data-to="${sumwos}" data-speed="1500"></h2>
                <p class="count-text ">WOS</p>`
        document.getElementById("tci").innerHTML += `
                <i class="count-icon fa fa-book fa-2x"></i>
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
<script>
    $(document).on('click', '.open_modal', function() {
        //var url = "domain.com/yoururl";
        var tour_id = $(this).val();
        $.get('/bib/' + tour_id, function(data) {
            //success data
            console.log(data);
            $( ".bibtex-biblio" ).remove();
            document.getElementById("name").innerHTML += `${data}`
            // $('#tour_id').val(data.id);
            // $('#name').val(data);
            // $('#details').val(data.details);
            // $('#btn-save').val("update");
            $('#myModal').modal('show');
        })
    });
</script>
@endsection