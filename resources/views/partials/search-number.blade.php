<div class="w-100 py-3 mt-4 bg-white rounded table-responsive">
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
                <th class="min90"><b>Label</b></th> 
                <th class="min95"><b>Issue</b></th>
                <th class="min185"><b>Created At</b></th>
                <th class="min70"><b>Tools</b></th>
            </tr>
        </thead>

        <tbody>
            <tr id="order-{{ $orders->id }}">
                <td class="cb">
                    <input class="form-check-input order-checker pointer" id="{{ $orders->id }}"
                        data-file="{{ $orders->name }}" type="checkbox" value="{{ $orders->id }}"
                        onchange="checkOrder(event)">
                </td>
                <td class="min95">
                    <b class="text-info">{{ config('app.custom.' . $orders->pending) }}</b>
                </td>
                <td class="ord_numb">{{ $orders->name }}</td>
                <td class="printfile accordion-toggle" data-toggle="collapse" data-target="#collapse_1">
                    <b class="darkBlue">Expand Files</b><i class="fa fa-caret-down ml-1"></i>
                </td>
                <td class="quantity">{{ count($orders->files) }}</td>
                <td class="min95">{{ $orders->files[0]->sku }}</td>
                <td class="min90">
                    @if (file_exists(public_path('archive/' . $orders->name . '/label.pdf')))
                        <a href="{{ route('get.pdf', [str_replace('#', '', $orders->name), 'label.pdf']) }}" target="_blank">
                            <b>Label</b>
                        </a>
                    @endif
                </td>
                <td class="min95">
                    @if (!is_null($orders->issue))
                        <span class="badge badge-info p-2 text-white rounded-pill">{{ $orders->issue }}</span>
                    @endif
                </td>
                <td class="min180">{{ $orders->created_at }}</td>
                <td class="min70">
                    @if ($orders->pending == 0 || $orders->pending == 1)
                        <a href="#" onclick="showEditModal(event, {{ $orders->id }})"><i class="fa fa-pencil-alt text-warning"></i></a>
                    @else
                        <i class="fa fa-pencil-alt text-secondary"></i>
                    @endif
                </td>                    
            </tr>
            <tr>
                <td colspan="12" class="hiddenRow">
                    <div class="collapse" id="collapse_1" data-parent="#accor">
                        <table class="table table-condensed table-details">
                            <tbody>
                                @foreach ($orders->files as $n=>$file)
                                    <tr class="table-details">
                                        <td class="cb">&nbsp;</td>
                                        <td class="min95">&nbsp;</td>
                                        <td class="ord_numb">&nbsp;</td>


                                        <td class="printfile position-relative" id="printfile_{{$file->id}}">
                                            <div class="position-relative">
                                                <img src="{{ asset('uploads/thumbs/' . $file->image) }}">
                                                @if($orders->pending == 0 || $orders->pending == 1)
                                                    <div class="replace-image">
                                                        <a href="#" data-toggle="modal" data-target="#replaceImage" data-orderdataid="{{$file->id}}" data-imagen="{{$n}}" data-key="image" data-backdrop="static" data-keyboard="false">
                                                            Replace
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                            @for($i=2; $i<=4; $i++)
                                                @if($file->{'image_'.$i})
                                                    <div class="position-relative">
                                                        <img src="{{ asset('uploads/thumbs/' . $file->{'image_'.$i}) }}">
                                                        @if($orders->pending == 0 || $orders->pending == 1)
                                                            <div class="replace-image">
                                                                <a href="#" data-toggle="modal" data-target="#replaceImage" data-orderdataid="{{$file->id}}" data-imagen="{{$n}}" data-key="image_{{$i}}" data-backdrop="static" data-keyboard="false">
                                                                    Replace
                                                                </a>
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            @endfor
                                        </td>



                                        <td class="min95">{{ $file->quantity }}</td>
                                        <td class="min95">{{ $file->sku }}</td>
                                        <td class="min90">&nbsp;</td>
                                        <td class="min95">&nbsp;</td>
                                        <td class="min185">&nbsp;</td>
                                        <td class="min70">&nbsp;</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
            
        </tbody>
    </table>

    @empty($orders)
        <h3 class="w-100 text-center my-4"><i class="fa fa-info-circle me-3"></i>Nothing here</h3>
    @endempty
</div>
