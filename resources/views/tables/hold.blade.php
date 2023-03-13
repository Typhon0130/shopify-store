<div class="w-100 py-3 mt-4 bg-white rounded table-responsive">
    @if (isset($orders) && count($orders) > 0)
        <table class="table table-condensed listing-table" id="accor">
            <thead>
                <tr>                   
                    <th class="cb">
                        <input class="form-check-input pointer" type="checkbox" onchange="selectAll(event)">
                    </th>                    
                    <th class="ord_numb"><b>Order Number</b></th>
                    <th class="printfile"><b>Print File</b></th>
                    <th class="min95"><b>Quantity</b></th>
                    <th class="min95"><b>Status</b></th>
                    <th class="min115"><b>SKU</b></th>                    
                    <th class="min185"><b>Created At</b></th>
                </tr>
            </thead>
            
            <tbody>
                @foreach ($orders as $key => $order)
                    <tr id="order-{{ $order->id }}">                        
                        <td class="cb d-flex omv-class-{{ $order->pending }}">
                            <input id="{{ $order->id }}" class="form-check-input order-checker pointer"  onchange="checkOrder(event)"
                                type="checkbox" value="{{ $order->id }}" data-file="{{ $order->name }}">                                
                        </td>
                        <td class="ord_numb">{{ $order->name }}</td>
                        <td class="printfile accordion-toggle" data-toggle="collapse" data-target="#collapse_{{ $key }}">
                            <b class="darkBlue">Expand Files</b><i class="fa fa-caret-down ml-1"></i>
                        </td>
                        <td class="min95">{{ count($order->files) }}</td>
                        <td class="min95">On hold</td>
                        <td class="min115">{{ $order->files[0]->sku }}</td>
                        <td class="min185">{{ $order->created_at }}</td>                        
                    </tr>
                    <tr class="child-{{ $order->id }}">
                        <td colspan="10" class="hiddenRow">
                            <div class="collapse" id="collapse_{{ $key }}" data-parent="#accor">
                                <table class="table table-condensed table-details">
                                    <tbody>
                                        @foreach ($order->files as $file)
                                            <tr class="table-details">                                                
                                                <td class="cb"></td>                                                
                                                <td class="ord_numb">
                                                    @if (!is_null($order->issue) && !is_null(json_decode($order->full_order)->note))
                                                        <a href="{{ json_decode($order->full_order)->note }}" target="_blank">Proof</a>
                                                    @endif
                                                </td>
                                                <td class="printfile" id="pf_{{$file->id}}">
                                                    @if($file->image == null)
                                                        {{-- <img src="{{ asset('uploads/thumbs/' . $file->image) }}" data-toggle="modal" data-target="#changeImageModal" 
                                                            data-imageId="{{ $file->id }}" id="listing{{ $file->id }}" style="cursor: pointer; width:150px">  --}}
                                                    
                                                        <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#uploadHoldImage" 
                                                            data-ordername="{{ $order->name }}" data-backdrop="static" data-keyboard="false" data-imageId="{{ $file->id }}" id="listing{{ $file->id }}">Upload image...</button>
                                                    @else
                                                        <img src="{{ asset('uploads/thumbs/' . $file->image) }}">
                                                    @endif
                                                </td>
                                                <td class="min95">
                                                    {{ $file->quantity }}
                                                </td>
                                                <td class="min95" id="st_{{$file->id}}">
                                                    @if($file->image == null) 
                                                        On hold
                                                    @endif
                                                </td>
                                                <td class="min115" id="sku_{{$file->id}}">
                                                    {{ $file->sku }}
                                                </td>
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
