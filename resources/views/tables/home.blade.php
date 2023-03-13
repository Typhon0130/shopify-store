<div class="w-100 py-3 mt-4 bg-white rounded table-responsive">
    @if (isset($orders) && count($orders) > 0)
        <table class="table table-condensed listing-table" id="accor">
            <thead>
                <tr>                    
                    <th class="cb"><input class="form-check-input pointer" type="checkbox" onchange="selectAll(event)"></th>                    
                    <th class="ord_numb"><b>Order Number</b></th>
                    <th class="printfile"><b>Print File</b></th>
                    <th class="min95"><b>Quantity</b></th>
                    <th class="min115"><b>SKU</b></th>
                    <th class="min95"><b>Issue</b></th>

                    @if ( Request::segment(2) == 'home' && Auth::User()->company_id > 0)
                        <th class="min70"><b>Fix</b></th>
                        <th class="min95"><b>Fee</b></th>
                        <th class="55"><i class="fa fa-eye"></i></th>
                    @endif

                    <th class="min185"><b>Created At</b></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($orders as $key => $order)
                    <tr id="order-{{ $order->id }}">                        
                        <td class="cb d-flex omv-class-{{ $order->pending }}">
                            <input id="{{ $order->id }}" class="form-check-input order-checker pointer" type="checkbox" value="{{ $order->id }}"
                                data-paysum="{{ $order->psum }}" data-file="{{ $order->name }}" onchange="checkOrder(event)">
                        </td>                       
                        <td class="ord_numb">{{ $order->name }}</td>
                        <td class="printfile accordion-toggle" data-toggle="collapse" data-target="#collapse_{{ $key }}">
                            <b class="darkBlue">Expand Files</b><i class="fa fa-caret-down ml-1"></i>
                        </td>
                        <td class="min95">
                            {{ count($order->files) }}
                        </td>
                        <td class="min115">
                            {{ $order->files[0]->sku }}
                        </td>                        
                        <td class="min95">
                            @if (!is_null($order->issue))
                                <span class="badge badge-info p-2 text-white rounded-pill">{{ $order->issue }}</span>
                            @endif
                        </td>
                        

                        @if ( Request::segment(2) == 'home' && Auth::User()->company_id > 0)
                            <td class="min70"><button class="btn btn-info btn-sm-height" onclick="fixOrder({{$order->id}})">Fix</button></td>
                            <td class="min95">{{ $order->psum }}</td>
                            <td class="min55"><i class="fa {{($order->paid_FR && $order->paid_PS)? 'fa-eye text-success' : 'fa-eye-slash text-danger'}}"></i></td>
                        @endif


                        <td class="min185 d-flex">
                            <span class="mr-auto" style=" white-space: nowrap">{{ $order->created_at }}</span>
                            @if(Auth::user()->administrator)
                                <span data-toggle="modal" data-target="#delOrderModal" data-orderId="{{ $order->id }}" data-orderName="{{ $order->name }}">
                                    <i class="fa fa-trash text-danger del-order"></i>
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td colspan="10" class="hiddenRow">
                            <div class="collapse" id="collapse_{{ $key }}" data-parent="#accor">
                                <table class="table table-condensed table-details">
                                    <tbody>
                                        @foreach ($order->files as $n=>$file)
                                            <tr class="table-details">
                                                <th class="cb"></th>                                                
                                                <td class="ord_numb">
                                                    @if (!is_null($order->issue) && !is_null(json_decode($order->full_order)->note))
                                                        <a href="{{ json_decode($order->full_order)->note }}" target="_blank">Proof</a>
                                                    @endif
                                                </td>
                                                <td class="printfile position-relative" id="printfile_{{$file->id}}">
                                                    <div class="position-relative">
                                                        <img src="{{ asset('uploads/thumbs/' . $file->image) }}">
                                                        <div class="replace-image">
                                                            <a href="#" data-toggle="modal" data-target="#replaceImage" data-orderdataid="{{$file->id}}" data-imagen="{{$n}}" data-key="image" data-backdrop="static" data-keyboard="false">
                                                                Replace
                                                            </a>
                                                        </div>
                                                    </div>
                                                    @for($i=2; $i<=4; $i++)
                                                        @if($file->{'image_'.$i})
                                                            <div class="position-relative">
                                                                <img src="{{ asset('uploads/thumbs/' . $file->{'image_'.$i}) }}">
                                                                <div class="replace-image">
                                                                    <a href="#" data-toggle="modal" data-target="#replaceImage" data-orderdataid="{{$file->id}}" data-imagen="{{$n}}" data-key="image_{{$i}}" data-backdrop="static" data-keyboard="false">
                                                                        Replace
                                                                    </a>
                                                                </div>
                                                            </div>
                                                        @endif
                                                    @endfor
                                                </td>
                                                <td class="min95">
                                                    {{ $file->quantity }}
                                                </td>
                                                <td class="min115">
                                                    {{ $file->sku }}
                                                </td>
                                                <td class="min95"></td>


                                                @if ( Request::segment(2) == 'home' && Auth::User()->company_id > 0)
                                                    <td class="min70"></td>{{-- fix --}}
                                                    <td class="min95"></td>
                                                    <td class="min55"></td>
                                                @endif


                                                <td class="min185"></td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="d-flex">
            <h2 class="text-left my-3 mx-3 text-info"><i class="fa fa-info-circle me-3 mx-2"></i></h2>
            <div class="d-flex align-items-center"><span class="text-info h5">No items in this category.</span></div>
        </div>
    @endif
</div>

<div class="w-100 pb-3 mt-4 rounded">
    {!! $orders->onEachSide(3)->links('extra.pagination') !!}
</div>
