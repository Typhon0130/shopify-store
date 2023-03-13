<div class="w-100 py-3 mt-4 bg-white rounded table-responsive">
    @if (isset($orders) && count($orders) > 0)
        <table class="table table-condensed listing-table" id="accor">
            <thead>
                <tr>
                    @if($type!=='dashboard')
                        <th class="cb">
                            <input class="form-check-input pointer" type="checkbox" onchange="selectAll(event)">
                        </th>
                    @else
                        <td class="cb-0"></td>
                    @endif
                    <th class="ord_numb"><b>Order Number</b></th>
                    <th class="printfile"><b>Print File</b></th>
                    <th class="min95"><b>Quantity</b></th>
                    <th class="min115"><b>SKU</b></th>
                    @if ($type == 2 && $type != 'dashboard')
                    <th class="min90"><b>Label</b></th>
                    @endif
                    
                    @if ($type != 0 || $type == 'dashboard')
                        <th class="min95"><b>Issue</b></th>
                    @endif
                    
                    <th class="min185"><b>Created At</b></th>
                    @if ($type == 1)
                    <th class="min70"><b>Tools</b></th>
                    @endif
                </tr>
            </thead>
            
            <tbody>
                @foreach ($orders as $key => $order)
                    <tr class="order-{{ $order->id }}">
                        @if($type!=='dashboard')
                            <td class="cb d-flex omv-class-{{ $order->pending }}">
                                <input id="{{ $order->id }}" class="form-check-input order-checker pointer" 
                                    type="checkbox" value="{{ $order->id }}" data-file="{{ $order->name }}"
                                    onchange="checkOrder(event)">                                
                            </td>
                        @else
                            <td class="cb-0">
                                <div class="omv-class-{{$order->pending}}"></div>
                            </td>
                        @endif
                        <td class="ord_numb">{{ $order->name }}</td>
                        <td class="printfile accordion-toggle" data-toggle="collapse"
                            data-target="#collapse_{{ $key }}">
                            <b class="darkBlue">Expand Files</b><i class="fa fa-caret-down ml-1"></i>
                        </td>
                        <td class="min95">
                            {{ count($order->files) }}
                        </td>
                        <td class="min115">
                            {{ $order->files[0]->sku }}
                        </td>
                        @if ($type == 2 && $type != 'dashboard')
                            <td class="min90">
                                @if (file_exists(public_path('archive/' . $order->name . '/label.pdf')))
                                    <div class="w-100">
                                        <a href="{{ route('get.pdf', [str_replace('#', '', $order->name), 'label.pdf']) }}"
                                            target="_blank"><b>Label</b></a>
                                        @if ($order->country_code == 'CH')
                                            <a href="{{ route('get.pdf', [str_replace('#', '', $order->name), 'exportLabel.pdf']) }}"
                                                class="d-block w-100 mt-3 text-warning" target="_blank">
                                                <b>Export Document</b>
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </td>
                        @endif

                        @if ($type != 0 || $type == 'dashboard')
                            @if (!is_null($order->issue))
                                <td class="min95">
                                    <span class="badge badge-info p-2 text-white rounded-pill">{{ $order->issue }}</span>
                                </td>
                            @else
                                <td class="col-1"></td>
                            @endif
                        @endif

                        <td class="min185">{{ $order->created_at }}</td>
                        @if ($type == 1)
                            <td class="min70">
                                <a href="#" onclick="showEditModal(event, {{ $order->id }})">
                                    <i class="fa fa-pencil-alt text-warning"></i>
                                </a>
                            </td>
                        @endif
                    </tr>
                    <tr>
                        <td colspan="10" class="hiddenRow">
                            <div class="collapse" id="collapse_{{ $key }}" data-parent="#accor">
                                <table class="table table-condensed table-details">
                                    <tbody>
                                        @foreach ($order->files as $file)
                                            <tr class="table-details">                                                
                                                @if($type!=='dashboard')
                                                    <th class="cb"></th>
                                                @else
                                                    <td class="cb-0"></td>
                                                @endif
                                                <td class="ord_numb">
                                                    @if (!is_null($order->issue) && !is_null(json_decode($order->full_order)->note))
                                                        <a href="{{ json_decode($order->full_order)->note }}" target="_blank">Proof</a>
                                                    @endif
                                                </td>
                                                <td class="printfile">
                                                    @if($file->hold !== null)
                                                        <img src="{{ asset('uploads/thumbs/' . $file->image) }}" data-toggle="modal" data-target="#changeImageModal" data-imageId="{{ $file->id }}" id="listing{{ $file->id }}" style="cursor: pointer; width:150px">                                                       
                                                    @else
                                                        <img src="{{ asset('uploads/thumbs/' . $file->image) }}">
                                                    @endif
                                                </td>
                                                <td class="min95">
                                                    {{ $file->quantity }}
                                                </td>
                                                <td class="min115">
                                                    {{ $file->sku }}
                                                </td>
                                                @if ($type == 2 && $type != 'dashboard')
                                                    <td class="min90"></td>
                                                @endif
                                                @if ($type != 0 || $type == 'dashboard')
                                                    <td class="min95"></td>
                                                @endif
                                                <td class="min185"></td>
                                                @if ($type == 1)
                                                    <td class="min70"></td>
                                                @endif
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
