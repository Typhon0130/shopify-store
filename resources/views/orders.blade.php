@extends('layouts.app')

@section('content')

    @include('includes.menu')

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="container-fluid mt-4 mb-2 ps-0">
            <h2>{{ $title }}</h2>
        </div>
<div class="d-flex">
        <form action="{{ route('search') }}" method="post" class="w-100">
            @csrf
            <div class="d-block d-lg-flex w-100 px-1 py-2">
                <div class="d-flex bg-white rounded flex-grow-1">

                    <div class="d-flex tablinks rounded">
                        @if (in_array('All', config('app.custom.' . Request::segment(2) . '.links')))
                            <a href="{{ route('orders', ['type' => Request::segment(2)]) }}"
                                class="d-block px-3 py-2 rounded bg-white {{Request::segment(3)==''?'link-active':'link-inactive'}}">All</a>
                        @endif

                        @if (in_array('Normal', config('app.custom.' . Request::segment(2) . '.links')))
                            <a href="{{ route('orders', ['type' => Request::segment(2), 'where' => 'orders']) }}"
                                class="d-block px-3 py-2 bg-white ms-3 {{Request::segment(3)=='orders'?'link-active':'link-inactive'}}">Normal</a>
                        @endif

                        @if (in_array('Issued', config('app.custom.' . Request::segment(2) . '.links')))
                            <a href="{{ route('orders', ['type' => Request::segment(2), 'where' => 'issued']) }}"
                                class="d-block px-3 py-2 bg-white ms-3 {{Request::segment(3)=='issued'?'link-active':'link-inactive'}}">Issued</a>
                        @endif

                        @if (in_array('DHL Rejected', config('app.custom.' . Request::segment(2) . '.links')))
                            <a href="{{ route('orders', ['type' => Request::segment(2), 'where' => 'dhl-rejected']) }}"
                                class="d-block px-3 py-2 bg-white ms-3 {{Request::segment(3)=='dhl-rejected'?'link-active':'link-inactive'}}">DHL Rejected</a>
                        @endif
                        
                        @if (in_array('Delivered', config('app.custom.' . Request::segment(2) . '.links')))
                            <a href="{{ route('orders', ['type' => Request::segment(2), 'where' => 'delivered']) }}"
                                class="d-block px-3 py-2 bg-white ms-3 {{Request::segment(3)=='delivered'?'link-active':'link-inactive'}}">Delivered</a>
                        @endif

                        @if (in_array('In transit', config('app.custom.' . Request::segment(2) . '.links')))
                            <a href="{{ route('orders', ['type' => Request::segment(2), 'where' => 'in-transit']) }}"
                                class="d-block px-3 py-2 bg-white ms-3 {{Request::segment(3)=='in-transit'?'link-active':'link-inactive'}}">In transit</a>
                        @endif

                        @if (in_array('Failed', config('app.custom.' . Request::segment(2) . '.links')))
                            <a href="{{ route('orders', ['type' => Request::segment(2), 'where' => 'failed']) }}"
                                class="d-block px-3 py-2 bg-white ms-3 {{Request::segment(3)=='failed'?'link-active':'link-inactive'}}">Failed</a>
                        @endif
                    </div>
                   
                    <div class="d-flex bg-white rounded ml-auto">
                        <input type="text" id="dateFrom" class="form-control border-0 w135" placeholder="From">
                        <i class="fa fa-calendar input-icon icon-right"></i>
                    </div>

                    <div class="d-flex bg-white rounded">
                        <input type="text" id="dateTo" class="form-control border-0 w135 ml-2" placeholder="To">
                        <i class="fa fa-calendar input-icon icon-right"></i>
                    </div>
                </div>                
                
            </div>
        </form>
                <div class="flex-row-reverse pt-2 pt-lg-0 d-flex buttons flex-grow-0">
                    <input type="hidden" name="type" value="date">
                    <div class="buttons d-flex py-2">
                        
                            <button type="button" class="btn btn-primary disabled ml-2" style="box-sizing: border-box"
                                onclick="download({{ $type }}{{ config('app.custom.' . Request::segment(2) . '.action') }})">Download</button>
                        
                        @if (config('app.custom.' . Request::segment(2) . '.button') == 'on')
                            <button type="button" class="btn btn-{{ config('app.custom.' . Request::segment(2) . '.class') }} ml-2"
                                onclick="{{ config('app.custom.' . Request::segment(2) . '.jsFunction') }}">
                                {{ config('app.custom.' . Request::segment(2) . '.text') }}
                            </button>
                        @endif
                        @if ( Request::segment(2) == 'home' && Auth::User()->company_name > 2)
                        <form method="post" action="{{route('pay.printfee')}}" id="pay-printfee">
                            @csrf
                            <button type="button" class="btn btn-primary ml-2 disabled" id="pay_paypal" >
                                <i class="fab fa-paypal mr-2"></i> Pay
                            </button>
                        </form>
                        @endif
                        
                        <button type="button" class="btn btn-primary disabled ml-2" style="box-sizing: border-box"
                                onclick="Export CSV({{ $type }}{{ config('app.custom.' . Request::segment(2) . '.action') }})">Export CSV</button>
                                
                      

                    </div>
                </div>
            </div>

        <div id="table">
            @include('tables.'.Request::segment(2))
        </div>

    </main>

@endsection
