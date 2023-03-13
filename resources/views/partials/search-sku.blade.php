<div class="w-100 pt-3 mt-4 mb-3 bg-white rounded table-responsive">
    <table class="table table-condensed listing-table" id="accor">
        <thead>
            <tr>
                <th class="cb">
                    <input class="form-check-input pointer" type="checkbox" onchange="selectAll(event)">
                </th>
                <th class="min95"><b>Order Type</b></th>
                <th class="ord_numb"><b>Order Number</b></th>
                <th class="printfile"><b>Print File</b></th>
                <th class="min95"><b>Quantity</b></th>
                <th class="min95"><b>SKU</b></th>
                <th class="min95"><b>Issue</b></th>
                <th class="min185"><b>Created At</b></th>
                <th class="min70"><b>Tools</b></th>
            </tr>
        </thead>

        <tbody>
            @foreach($orders as $key => $order)
                <tr id="order-{{ $order->parent->id }}">
                    <td class="cb">
                        <input class="form-check-input order-checker pointer" id="{{ $order->parent->id }}" 
                            data-file="{{ $order->parent->name }}" value="{{ $order->parent->id }}" type="checkbox" 
                            onchange="checkOrder(event)">
                    </td>
                    <td class="min95">
                        <b class="text-info">{{ config('app.custom.' . $order->parent->pending) }}</b>
                    </td>
                    <td class="ord_numb">{{ $order->parent->name }}</td>
                    <td class="printfile accordion-toggle" data-toggle="collapse" data-target="#collapse_{{ $key }}">
                        <b class="darkBlue">Expand Files</b><i class="fa fa-caret-down ml-1"></i>
                    </td>
                    <td class="quantity">{{ count($order->parent->files) }}</td>
                    <td class="min95">{{ $order->parent->files[0]->sku }}</td>
                    <td class="min95">
                        @if (!is_null($order->parent->issue))
                            <span class="badge badge-info p-2 text-white rounded-pill">{{ $order->parent->issue }}</span>
                        @endif
                    </td>
                    <td class="min180">{{ $order->parent->created_at }}</td>
                    <td class="min70">
                        @if($order->parent->pending == 1)
                            <a href="#"><i class="fa fa-pencil-alt text-warning"></i></a>
                        @else
                            <i class="fa fa-pencil-alt text-secondary"></i>
                        @endif
                    </td>                    
                </tr>
                <tr>
                    <td colspan="12" class="hiddenRow">
                        <div class="collapse" id="collapse_{{ $key }}" data-parent="#accor">
                            <table class="table table-condensed table-details">
                                <tbody>
                                    @foreach ($order->parent->files as $file)
                                        <tr class="table-details">
                                            <td class="cb"></td>
                                            <td class="min95"></td>
                                            <td class="ord_numb">                                                   
                                            </td>
                                            <td class="printfile mb-4">
                                                <img src="{{ asset('uploads/thumbs/' . $file->image) }}">
                                            </td>
                                            <td class="min95 mb-4">
                                                {{ $file->quantity }}
                                            </td>
                                            <td class="min95 mb-4">
                                                {{ $file->sku }}
                                            </td>                                                
                                            <td class="min95"></td>
                                            <td class="min185"></td>
                                            <td class="min70"></td>                                               
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

    @empty($orders)
        <h3 class="w-100 text-center my-4"><i class="fa fa-info-circle me-3"></i>Nothing here</h3>
    @endempty
</div>
<div class="w-100 py-3 mt-4 rounded">
    {!! $orders->onEachSide(3)->links('extra.pagination') !!}
</div>