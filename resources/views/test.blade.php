
New:     {{$data['new']}} <br>
Pending: {{$data['pending']}} <br>
Shipped: {{$data['shipped']}} <br>
Unfulfl: {{$data['unfulfilled']}} <br>
TOTAL: {{$data['total']}} <br><br><br>

@foreach ($orders as $i=>$order)
   {{$i+1}} {{$order->name}}  -  {{$order->created_at}}   -   {{$order->pending}}   -   {{$order->status}} <br>
@endforeach


@if( method_exists($orders,'links'))
   {{  $orders ->links('extra.pagination') }}
@endif 