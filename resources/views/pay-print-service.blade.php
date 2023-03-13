@extends('layouts.app')

@section('content')
    @include('includes.menu')

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="container-fluid mt-4 mb-2 ps-0">
            <h2>Payment details</h2>
        </div>
        
		<div class="w-100 p-3 mt-4 bg-white rounded table-responsive">
			<table class="table table-sm mb-0">
				<thead>
					<tr>
						<th><b>Order Name</b></th>
						<th><b>Total Price</b></th>
						<th><b>Total Print price</b></th>
						<th><b>Weight fee</b></th>
						<th><b>Sum</b></th>
					</tr>
				</thead>
				<tbody>
					@foreach ($orders as $key => $order)
						<tr>
							<td>{{$order['orderName']}}</td>
							<td>{{$order['price']}}</td>
							<td>{{$order['printPrice']}}</td>
							<td>{{$order['weightFee']}}</td>
							<td>{{$order['price'] + $order['printPrice'] + $order['weightFee']}}</td>
						</tr>
					@endforeach
					<tr>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td><b>Sum: {{$totalPrice}}</b></td>
					</tr>
				</tbody>
			</table>
			<div class="border-top pt-2">
				<script src="https://www.paypal.com/sdk/js?client-id={{env('PAYPAL_CLIENT_ID_PS', '')}}&currency=EUR"></script>
				<div id="paypal-button-container" class="mt-1" style="max-width: 100px"></div>
				<script>
					paypal.Buttons({
						createOrder: (data, actions) => {
							return actions.order.create({
								purchase_units: [{
									amount: {
										currency_code: "EUR",
										value: {{$totalPrice}},
										breakdown: {
											item_total: {
												currency_code: "EUR",
												value: {{$totalPrice}}
											}
										}
									},
									items: [{
										name: "Print service fee",
										description: "{{$paymentId}}",
										unit_amount: {
											currency_code: "EUR",
											value: {{$totalPrice}}
										},
										quantity: "1"
									}, ]
								}]
							});
						},
						onApprove: (data, actions) => {
							return actions.order.capture().then(function(orderData) {
								console.log('Capture result', orderData, JSON.stringify(orderData, null, 2), orderData.status);
								const orderId = orderData.id;
								window.location.href = "validate-ps-transaction/"+orderId;
							});
						},
						style: {
							layout: 'horizontal',
							size: 'small',
							tagline: 'false',
							height:40
						}
					}).render('#paypal-button-container');
				</script>
			</div>
		</div>
    </main>
@endsection