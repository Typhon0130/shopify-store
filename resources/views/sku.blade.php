@extends('layouts.app')

@section('content')

    @include('includes.menu')

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="container-fluid mt-4 ps-0">
            <h2 class="mb-2">{{ $title }}</h2>
            <button class="btn btn-secondary btn-sm" data-toggle="modal" data-target="#editProductModal" data-productid="0"><i class="fas fa-plus-square mr-2"></i> Add product</button>
        </div>

        <div class="w-100 py-3 px-4 mt-3 mb-4 bg-white rounded table-responsive">
            <table class="table table-condensed listing-table ">
                <thead>
                    <tr>
                        <th class="col-2"><b>Image</b></th>
                        <th class="col-2"><b>Product name</b></th>
                        <th colspan="5" class="col-6 p-1">
                            <table class="inner-table"><tbody><tr>
                                <th class="col-2"><b>SKU number</b></th>
                                <th class="col-2"><b>Size</b></th>
                                <th class="col-1"><b>Weight</b></th>
                                <th class="col-2"><b>Printfile size</b></th>
                                <th class="col-1"><b>Price</b></th>
                                <th class="col-1"><b>Print price</b></th>
                                <th class="col-1"><b>Stock quantity</b></th>
                            </tr></tbody></table>
                        </th>
                        <th class="col-1"><b>Edit</b></th>
                    </tr>
                </thead>

                <tbody id="main_table">                    
                    @foreach ($products as $key => $product)
                        <tr id="product-{{ $product->id }}">                         
                            <td class="col-2 p-2 product-image align-middle" id="productimage-{{ $product->id }}">
                                @if($product->image)
                                    <img src="{{ asset('uploads/sku/' . $product->image) }}">
                                @endif
                            </td>
                            <td class="col-2 align-middle text-secondary" id="productname-{{ $product->id }}">
                                {{$product->product_name}}
                            </td>
                            <td colspan="5" class="col-6 p-1 align-middle">
                                <table class="inner-table" >
                                    <tbody id="sku_tbody-{{ $product->id }}">
                                        @foreach ($product->sku as $sku)
                                            <tr id="skurow{{$sku->id}}">
                                                <td class="col-2 align-middle"> {{$sku->name}} </td>
                                                <td class="col-2 align-middle"> {{$sku->size}} </td>
                                                <td class="col-1 align-middle"> {{$sku->weight}} </td>
                                                <td class="col-2 align-middle"> {{$sku->printfile_size}} </td>
                                                <td class="col-1 align-middle"> {{$sku->price}} </td>
                                                <td class="col-1 align-middle"> {{$sku->print_price}} </td>
                                                <td class="col-1 align-middle"> {{$sku->stock_quantity}} </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                            <td class="col-1 align-middle"> 
                                <span data-toggle="modal" data-target="#editProductModal" data-productid="{{ $product->id }}" class="cursor-pointer">
                                    <i class="fa fa-pencil-alt text-warning"></i> 
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="w-100 pb-3 mt-4 rounded">
            {!! $products->onEachSide(3)->links('extra.pagination2') !!}
        </div>
    </main>

    @include('includes.editProductModal')
@endsection

