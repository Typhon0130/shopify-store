@extends('layouts.app')

@section('content')
    @include('includes.menu')

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="container-fluid mt-4 ps-0">
            <h2 class="mb-2">{{ $title }}</h2>
        </div>

        <div class="w-100 py-3 px-4 mt-3 mb-4 bg-white rounded ">
            <form action="{{ route('shipment.archive') }}" method="GET" class="row">
                <div class="form-group col-6">
                    <label for="search">Search</label>
                    <input type="text" class="form-control form-control-sm" id="search" name="search" placeholder="Search"
                        autocomplete="off" value="{{request()->query('search')}}">
                </div>
                <div class="form-group col-2">
                    <label for="dtFrom">From</label>
                    <input type="text" class="form-control form-control-sm" id="dtFrom" name="from" placeholder=""
                        autocomplete="off" value="{{request()->query('from')}}">
                </div>
                <div class="form-group col-2">
                    <label for="dtTo">To</label>
                    <input type="text" class="form-control form-control-sm" id="dtTo" name="to" placeholder=""
                        autocomplete="off" value="{{request()->query('to')}}">
                </div>
                <div class="form-group col-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm px-3 mr-2"><i class="fas fa-search mr-2"></i>Search</button>
                    <a href="{{ url('downloadExcel/xls?search='.request()->query('search').'&from='.request()->query('from').'&to='.request()->query('to'))}}" class="btn btn-success btn-sm px-3">
                        <i class="fas fa-file-excel mr-2"></i>Export
                    </a>
                </div>
            </form>

			
			@if($orders)
            <table class="table table-condensed table-hover shipment-archive">
                <thead>
                    <tr>
                        <th scope="col">Shipment number</th>
                        <th scope="col">Shipment reference</th>
                        <th scope="col">Recipient name</th>
                        <th scope="col">Billing number</th>
                        <th scope="col">Shipping Date</th>
                        <th scope="col">Type</th>
                        <th scope="col">Last modified by</th>
                        <th scope="col"></th>
                    </tr>
                </thead>
                <tbody>
					@foreach($orders as $order)
						<tr>
							<td>{{$order->shipment_order}}</th>
							<td>{{$order->name}}</td>
							<td>{{$order->first_name . ' ' . $order->last_name}}</td>
							<td>63329499840101</td>
							<td>{{$order->calledDHL_at}}</th>
							<td>WS</th>
							<td>ccfrapa</th>
							<td>
								<a href="#" data-toggle="modal" data-target="#orderDetails" data-orderid="{{$order->id}}" data-ordername="{{$order->name}}"
                                    data-toggle="tooltip" data-placement="top" title="Order details">							
									<i class="fa fa-search mr-2"></i>
								</a>
                                @if (file_exists(public_path('archive/' . $order->name . '/label.pdf')))                                   
                                    <a href="{{ route('get.pdf', [str_replace('#', '', $order->name), 'label.pdf']) }}"
                                        target="_blank" data-toggle="tooltip" data-placement="top" title="Label">
                                        <i class="fas fa-print text-info mr-2"></i>
                                    </a>
                                @endif
								<a href="#" data-toggle="modal" data-target="#trackModal" data-shipment="{{$order->shipment_order}}" data-toggle="tooltip" data-placement="top" title="Tracking">
									<i class="fas fa-truck text-warning"></i>
								</a>
							</td>
						</tr>
					@endforeach
                </tbody>
            </table>
			@else
				<p>No result for searched criteria.</p>
			@endif
        </div>
        <div class="w-100 pb-3 mt-4 rounded">
            {{-- {!! $products->onEachSide(3)->links('extra.pagination2') !!} --}}
            {{ $orders->withQueryString()->onEachSide(3)->links('extra.pagination2') }}
        </div>
    </main>

    @include('includes.editProductModal')
@endsection
