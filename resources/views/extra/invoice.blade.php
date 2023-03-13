<!DOCTYPE html>
<html>

<head>
    <link href="{{ asset('assets/css/bootstrap.min.css') }}" rel="stylesheet">
    <style>
        body {
            font-family: 'Lato', sans-serif;
            font-size: .8rem
        }
        .table td, .table th {
            padding: 0.5rem;
        }

    </style>
</head>

<body>

    <div class="w-100 pb-5" style="background-color: white">
        <div>
            <h2>Report</h2>
        </div>
        <div class="mb-3">
            <span>For: {{ date('d F Y', strtotime($from))}} - {{ date('d F Y', strtotime($to)) }}</span>
        </div>

        <table class="table">
            <thead>
                <tr>
                    <th scope="col">No.</th>
                    <th scope="col">Product code and name</th>
                    <th scope="col">Quantity</th>
                    <th scope="col">Price</th>
                    <th scope="col">Print Price</th>
                    <th scope="col">Net Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($result as $i=>$res)
                    <tr>
                        <td>{{$i+1}}</td>
                        <td><strong>{{$res->sku}}</strong> <small>{{$res->product_name}}</small></td>
                        <td>{{$res->quantity}}</td>
                        <td>{{$res->price}}</td>
                        <td>{{$res->print_price}}</td>
                        <td>
                            {{$res->price&&$res->print_price
                                ? ($res->price+$res->print_price)*$res->quantity
                                : ''}}
                        </td>
                    </tr>
                @endforeach
                <tr>
                    <td colspan="4" class="bg-light"><strong>Summary of payment:</strong></td>                    
                    <td colspan="2" class="bg-light text-right"><strong>{{$netSum}} EUR</strong></td>
                </tr>
            </tbody>
        </table>
        @if($missingSKU)
            <span class="text-danger"> Some SKU Price and/or Print price are missing, sum might not be accurate!</span>
        @endif
    </div>
</body>

</html>
