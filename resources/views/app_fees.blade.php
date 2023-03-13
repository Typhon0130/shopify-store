@extends('layouts.app')

@section('content')
    @include('includes.menu')

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="container-fluid mt-4 mb-2 ps-0">
            <h2>{{ $title }}</h2>
        </div>

        @if(count($appFees))
            <div class="table-responsive bg-white my-3 p-2 rounded">
                <table class="table table-sm">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Action</th>
                        <th scope="col">Shop price</th>
                        <th scope="col">Rate</th>
                        <th scope="col">Fee</th>
                        <th scope="col">Date/Time</th>
                    </tr>
                    </thead>
                    <tbody>
                        @foreach($appFees as $i=>$appFee)
                            <tr>
                                <td>{{Request::get('page') * 30 + $i + 1}}</td>
                                <td>{{$appFee->order_name}}</td>
                                <td>{{$appFee->shop_price > 0 ? $appFee->shop_price : ''}}</td>
                                <td>{{$appFee->rate > 0 ? $appFee->rate : ''}}</td>
                                <td>
                                    <span class="text-{{$appFee->fee>0?'success':'danger'}}">{{$appFee->fee}}</span>
                                </td>
                                <td>{{$appFee->created_at}}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {!! $appFees->onEachSide(3)->links('extra.pagination2') !!}
        @endif

    </main>
@endsection