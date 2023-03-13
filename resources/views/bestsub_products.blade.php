@extends('layouts.app')

@section('content')
    @include('includes.menu')

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="container-fluid mt-4 mb-2 ps-0">
            <h2>{{ $title }}</h2>
        </div>

        <div class="w-100 py-3 mt-4 bg-white rounded table-responsive">

            <table class="table table-condensed listing-table" id="accor">
                <thead>
                    <tr>
                        <th><b>#</b></th>
                        <th><b>Image</b></th>
                        <th><b>Product Name</b></th>
                        <th><b>SKU</b></th>
                        <th><b>Quantity</b></th>
                        <th><b>Additional info</b></th>
                        <th><b>Updated</b></th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($products as $key => $product)
                        <tr>
                            <td class="align-middle">{{ $key + 1 }}</td>
                            <td class="align-middle"> <img src="{{ asset('foreign_products/' . $product->image) }}" width="150"></td>
                            <td class="align-middle">{{ $product->product_name }}</td>
                            <td class="align-middle">
                                @foreach (explode(",", $product->sku) as $sku)
                                    {!! $sku . "<br />" !!}
                                @endforeach
                            </td>
                            <td class="align-middle">{{ $product->quantity }}</td>
                            <td class="align-middle">{{ $product->additional_info }}</td>
                            <td class="align-middle">{{ $product->updated_at }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>

    </main>
@endsection
