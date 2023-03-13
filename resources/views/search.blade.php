@extends('layouts.app')

@section('content')
    
    @include('includes.menu')

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">

        <div class="container-fluid mt-4 mb-2 ps-0">
            <h2>Search</h2>
        </div>

        <div class="d-block d-lg-flex w-100 px-1 py-2">
            <div class="d-flex bg-white rounded flex-grow-1">
                @if($part=='search-sku')
                    <div class="d-flex bg-white rounded ml-auto">
                        <input type="text" id="dateFrom" class="form-control border-0 w135" placeholder="From">
                        <i class="fa fa-calendar input-icon icon-right"></i>
                    </div>

                    <div class="d-flex bg-white rounded">
                        <input type="text" id="dateTo" class="form-control border-0 w135 ml-2" placeholder="To">
                        <i class="fa fa-calendar input-icon icon-right"></i>
                    </div>
                @endif
            </div>

            <div class="flex-row-reverse pt-2 pt-lg-0 d-flex buttons flex-grow-0 ml-auto">
                <input type="hidden" name="type" value="date">
                <div class="buttons d-flex flex-row-reverse">
                    <button type="button" class="btn btn-primary disabled ml-2" style="box-sizing: border-box"
                        onclick="download({{ $type }}{{ config('app.custom.' . Request::segment(2) . '.action') }})">Download</button>
                    @if (config('app.custom.' . Request::segment(2) . '.button') == 'on')
                        <button type="button"
                            class="btn btn-{{ config('app.custom.' . Request::segment(2) . '.class') }} ml-2"
                            onclick="{{ config('app.custom.' . Request::segment(2) . '.jsFunction') }}">
                            {{ config('app.custom.' . Request::segment(2) . '.text') }}
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </main>
    
    <div class="col-md-9 ml-sm-auto col-lg-10 px-md-4" id="table">
        @include('partials.' . $part)
    </div>
    
@endsection