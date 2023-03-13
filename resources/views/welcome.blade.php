@extends('layouts.app')

@section('content')

    @include('includes.menu')

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="container-fluid px-0 mt-4">
            <div class="row mx-0">
                <div class="col-lg-6 d-flex align-items-stretch m-0 p-0">
                    <div class="row flex-grow-1 m-0 p-0 stats">
                        <div class="col-6 d-flex align-items-stretch m-0 p-0 pr-2">
                            <div class="w-100 bg-white rounded px-3 pt-4 pb-5 d-flex flex-column">
                                <h5 class="mb-5 pl-3">DHL Order List</h5>
                                <div class="my-2 mt-auto d-flex">
                                    <h3 class="text-dark text-right pr-1 flex2 d-flex flex-column-reverse">
                                        <b class="dhl-pending" id="pendingCnt">{{ str_pad($data['pending']['current'], 2, '0', STR_PAD_LEFT) }}</b>
                                    </h3>
                                    <div class="ms-3 pl-2 mt-1 flex5 d-flex flex-column">
                                        {{-- <span class="{{$data['pending']['color']}}">
                                            <i class="{{$data['pending']['icon']}}"></i>
                                            {{$data['pending']['ratio']}}%
                                        </span> --}}
                                        <b>Pending</b>
                                    </div>
                                </div>
                                <div class="d-flex mt-3">
                                    <h3 class="text-dark text-right pr-1 flex2 d-flex flex-column-reverse">
                                        <b class="dhl-shipped" id="shippedCnt">{{ str_pad($data['shipped']['current'], 2, '0', STR_PAD_LEFT) }}</b>
                                    </h3>
                                    <div class="ms-3 pl-2 mt-1 flex5 d-flex flex-column">
                                        <span class="{{$data['shipped']['color']}}" id="shippedPerc">
                                            {{-- <i class="{{$data['shipped']['icon']}}"></i> --}}
                                            {{$data['shipped']['ratio']}}%
                                        </span>
                                        <b>Shipped out</b>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-6 d-flex align-items-stretch m-0 p-0 pl-2">
                            <div class="w-100 bg-white rounded px-3 pt-4 pb-5 d-flex flex-column">
                                <h5 class="mb-5 pl-3">OMV Order List</h5>
                                <div class="my-2 mt-auto d-flex">
                                    <h3 class="text-dark text-right pr-1 flex2 d-flex flex-column-reverse">
                                        <b class="omv-new" id="newCnt">{{ str_pad($data['new']['current'], 2, '0', STR_PAD_LEFT) }}</b>
                                    </h3>
                                    <div class="ms-3 pl-2 mt-1 flex5 d-flex flex-column">
                                        <span class="{{$data['new']['color']}}" id="newPerc">
                                            <i class="{{$data['new']['icon']}}"></i>
                                            <span>{{$data['new']['ratio']}}%</span>
                                        </span>
                                        <b>New product</b>
                                    </div>
                                </div>
                                <div class="d-flex mt-3">
                                    <h3 class="text-dark text-right pr-1 flex2 d-flex flex-column-reverse">
                                        <b class="omv-unfulfilled" id="unffilledCnt">{{ str_pad($data['unfulfilled']['current'], 2, '0', STR_PAD_LEFT) }}</b>
                                    </h3>
                                    <div class="ms-3 pl-2 mt-1 flex5 d-flex flex-column">
                                        <span class="{{$data['unfulfilled']['color']}}" id="unffilledPerc">
                                            {{-- <i class="{{$data['unfulfilled']['icon']}}"></i> --}}
                                            <span>{{$data['unfulfilled']['ratio']}}%</span>
                                        </span>
                                        <b>Unfulfilled</b>
                                    </div>
                                </div>
                            </div>
                        </div>                        
                    </div>
                </div>
                <div class="col-lg-6 m-0 mt-4 mt-lg-0 p-0 pl-lg-3">
                    <div class="w-100 bg-white rounded p-3 d-inline-block">
                        <div class="calendar-container"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-100 rounded">
            
            {{-- <table class="table table-condensed">
                <thead>
                    <tr>
                        <th class="col-2"><b>Order Number</b></th>
                        <th class="col-2"><b>Print File</b></th>
                        <th class="col-2"><b>Quantity</b></th>
                        <th class="col-2"><b>SKU</b></th>
                        <th class="col-1"><b>Issue</b></th>
                        <th class="col-3"><b>Created At</b></th>
                    </tr>
                </thead>


            <ul class="list-group orders">{!! $orders->html !!}</ul> --}}

            <div id="table">
                @include('table')
            </div>



        </div>

        


       
    </main>

@endsection
