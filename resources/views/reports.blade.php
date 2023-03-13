@extends('layouts.app')

@section('content')
    
    @include('includes.menu')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
            <div class="container-fluid mt-4 ps-0">
                <h2>{{ ucfirst(Request::segment(2)) . ' ' . $title }}</h2>
            </div>

            <div class="w-100 py-2 px-4 mt-4 d-flex bg-white rounded reports-nav">
                <a href="#" class="mx-2 {{ Request::segment(2) == 'recived' ? 'active-report-type' : null }}">Recived</a>
                <a href="#" class="mx-2 {{ Request::segment(2) == 'rejected' ? 'active-report-type' : null }}">Rejected</a>
                <a href="#" class="mx-2 {{ Request::segment(2) == 'finished' ? 'active-report-type' : null }}">Finished</a>
            </div>

            <div class="w-100 py-3 px-4 mt-4 bg-white">
                <ul class="list-group mb-4">
                    <li class="list-group-item border-0">
                        <div class="row">
                            <div class="align-items-center col-4">
                                <b class="w-100 d-block mb-3">{{ date("M Y") }}</b>
                                @foreach($orders as $order)
                                    @if (count($order) > 0)
                                        <div class="row">
                                            <div class="col-6 mt-4">{{ date('d', strtotime($order[0]->created_at)) . '-' . date('d', strtotime($order[count($order) - 1]->created_at)) }}</div>
                                            <div class="col-6 mt-4">{{ count($order) }} Product</div>
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                            <div class="align-items-center col-4">
                                <b class="w-100 d-block mb-3">All product chart</b>
                                <div class="w-100">
                                    {{-- <canvas id="chart"></canvas> --}}
                                </div>
                            </div>
                            <div class="align-items-center col-4"><b>Geographical</b></div>
                        </div>
                    </li>
                </ul>
            </div>

            <div class="container-fluid my-3 bg-white rounded reports">
                <div class="row">
                    <div class="col-md-12 col-lg-4 d-flex flex-column">
                        <div class="header d-flex justify-content-between">
                            <div class="month" id="month">
                                
                            </div>
                            <div class="arrows">
                                <i class="fas fa-angle-left mr-1" id="prevMonth"></i>
                                <i class="fas fa-angle-right ml-1" id="nextMonth"></i>
                            </div>
                        </div>
                        <div class="week active" data-week="week-1-7">
                            <span>1-7</span>
                            <span id="week1"></span>
                        </div>
                        <div class="week" data-week="week-8-15">
                            <span>8-15</span>
                            <span id="week2"></span>
                        </div>
                        <div class="week" data-week="week-16-23">
                            <span>16-23</span>
                            <span id="week3"></span>
                        </div>
                        <div class="week" data-week="week-24-31">
                            <span>24-31</span>
                            <span id="week4"></span>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-4">
                        <div class="w-100">
                            <canvas id="chart"></canvas>
                        </div>
                    </div>
                    <div class="col-md-12 col-lg-4" id="country-table">
                         <div class="header pl-2 mb-1">Geographical</div>
                         <table class="table table-borderless mb-0">
                            <thead>
                              <tr>
                                <th scope="col">Country</th>
                                <th scope="col">Orders</th>
                                <th scope="col">Price</th>
                              </tr>
                            </thead>
                            <tbody id="country-data">
                              {{-- <tr>
                                <td>Germany</td>
                                <td>154</td>
                                <td>1544$</td>
                              </tr>
                              <tr>
                                <td>Austria</td>
                                <td>154</td>
                                <td>1544$</td>
                              </tr>
                              <tr>
                                <td>Switzerland</td>
                                <td>154</td>
                                <td>1544$</td>
                              </tr> --}}
                            </tbody>
                          </table>
                    </div>
                </div>
            </div>

            <div class="container-fluid my-3 report-cards">
                <div class="row">
                    <div class="report-card">
                        <div class="card-content">
                            <div class="card-title">
                                B101AA
                            </div>
                            <div class="week-trade">
                                <div class="week-amount">
                                    140
                                </div>
                                <div class="week-change">
                                    <i class="fas fa-caret-up"></i>
                                    12%
                                </div>
                            </div>
                            <div class="month-year">
                                <div class="month">
                                    <div class="month-amount">
                                        140
                                    </div>
                                    <div class="month-change">                                        
                                        <span><i class="fas fa-caret-up"></i>12%</span>
                                        <span>Month</span>
                                    </div>
                                </div>
                                <div class="year">
                                    <div class="year-amount">
                                        140
                                    </div>
                                    <div class="year-change">
                                        <span><i class="fas fa-caret-up"></i>12%</span>
                                        <span>Year</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="report-card">
                        <div class="card-content">
                            <div class="card-title">
                                B101AA
                            </div>
                            <div class="week-trade">
                                <div class="week-amount">
                                    140
                                </div>
                                <div class="week-change">
                                    <i class="fas fa-caret-up"></i>
                                    12%
                                </div>
                            </div>
                            <div class="month-year">
                                <div class="month">
                                    <div class="month-amount">
                                        140
                                    </div>
                                    <div class="month-change">                                        
                                        <span><i class="fas fa-caret-up"></i>12%</span>
                                        <span>Month</span>
                                    </div>
                                </div>
                                <div class="year">
                                    <div class="year-amount">
                                        140
                                    </div>
                                    <div class="year-change">
                                        <span><i class="fas fa-caret-up"></i>12%</span>
                                        <span>Year</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="report-card">
                        <div class="card-content">
                            <div class="card-title">
                                B101AA
                            </div>
                            <div class="week-trade">
                                <div class="week-amount">
                                    140
                                </div>
                                <div class="week-change">
                                    <i class="fas fa-caret-up"></i>
                                    12%
                                </div>
                            </div>
                            <div class="month-year">
                                <div class="month">
                                    <div class="month-amount">
                                        140
                                    </div>
                                    <div class="month-change">                                        
                                        <span><i class="fas fa-caret-up"></i>12%</span>
                                        <span>Month</span>
                                    </div>
                                </div>
                                <div class="year">
                                    <div class="year-amount">
                                        140
                                    </div>
                                    <div class="year-change">
                                        <span><i class="fas fa-caret-up"></i>12%</span>
                                        <span>Year</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="report-card">
                        <div class="card-content">
                            <div class="card-title">
                                B101AA
                            </div>
                            <div class="week-trade">
                                <div class="week-amount">
                                    140
                                </div>
                                <div class="week-change">
                                    <i class="fas fa-caret-up"></i>
                                    12%
                                </div>
                            </div>
                            <div class="month-year">
                                <div class="month">
                                    <div class="month-amount">
                                        140
                                    </div>
                                    <div class="month-change">                                        
                                        <span><i class="fas fa-caret-up"></i>12%</span>
                                        <span>Month</span>
                                    </div>
                                </div>
                                <div class="year">
                                    <div class="year-amount">
                                        140
                                    </div>
                                    <div class="year-change">
                                        <span><i class="fas fa-caret-up"></i>12%</span>
                                        <span>Year</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="report-card"></div>
                    <div class="report-card"></div>
                    <div class="report-card"></div>
                </div>
            </div>


        </main>




<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>



legend: {
    display: false
}

const labels = [];

const data = {
    labels: labels,
    datasets: [{
        backgroundColor: 'rgb(255, 255, 255)',
        borderColor: '#4264d0',
        data: [],
        borderWidth: 2,
        tension: 0.4
    }]
};

const config = {
    type: 'line',
    data: data,
    options: {
        plugins: {
            legend: {
                display: false,
            }
        },
        scales: {
            x: {
                grid:{
                    display:false
                }
            },
            y: {
                grid:{
                    display:false
                }
            }
        }
    }
};

var myChart = new Chart(
    document.getElementById('chart'),
    config
);


</script>




@endsection