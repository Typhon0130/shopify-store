@extends('layouts.app')

@section('content')

    @include('includes.menu')
    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-md-4">
        <div class="container-fluid mt-4 ps-0">
            <h2>{{ $title }}</h2>
        </div>

        <div class="w-100 py-3 mt-4 bg-white rounded table-responsive">

            <table class="table table-condensed listing-table" id="accor">
                <thead>
                    <tr>
                        <th class="col-1">
                            <input class="form-check-input pointer" type="checkbox" onchange="selectAll(event)">
                        </th>
                        <th class="col-2"><b>Order Number</b></th>
                        <th class="col-2"><b>Print File</b></th>
                        <th class="col-2"><b>Quantity</b></th>
                        <th class="col-2"><b>SKU</b></th>
                        <th class="col-2"><b>Created At</b></th>
                        @if ($type == 1)
                            <th class="col-1"><b>Tools</b></th>
                        @endif
                    </tr>
                </thead>

                <tbody>
                    @if (isset($downloads) && count($downloads) > 0)
                        @foreach ($downloads as $key => $download)
                            <tr id="order-{{ $download->order->id }}">
                                <td class="d-flex col-1 omv-class-{{ $download->order->pending }}">
                                    <input id="{{ $download->order->id }}" data-file="{{ $download->order->name }}"
                                        class="form-check-input order-checker pointer" type="checkbox"
                                        value="{{ $download->order->id }}" onchange="checkOrder(event)">
                                </td>
                                <td class="col-2">{{ $download->order->name }}</td>
                                <td class="col-2 accordion-toggle" data-toggle="collapse"
                                    data-target="#collapse_{{ $key }}">
                                    <b class="darkBlue">Expand Files</b><i class="fa fa-caret-down ml-1"></i>
                                </td>
                                <td class="col-2">{{ count($download->order->files) }}</td>
                                <td class="col-2">{{ $download->order->files[0]->sku }}</td>
                                <td class="col-2">{{ $download->order->created_at }}</td>
                                @if ($type == 1)
                                    <td class="col-1 d-flex">
                                        <a href="#" onclick="showEditModal(event, {{ $download->order->id }})">
                                            <i class="fa fa-pencil-alt text-warning"></i>
                                        </a>
                                    </td>
                                @endif

                            <tr>
                                <td colspan="8" class="hiddenRow">
                                    <div class="collapse" id="collapse_{{ $key }}" data-parent="#accor">
                                        <table class="table table-condensed table-details">
                                            <tbody>
                                                @foreach ($download->order->files as $file)
                                                    <tr class="table-details">
                                                        <td class="col-1"></td>
                                                        <td class="col-2"></td>
                                                        <td class="col-2">
                                                            <img src="{{ asset('uploads/thumbs/' . $file->image) }}">
                                                        </td>
                                                        <td class="col-2">{{ $file->quantity }}</td>
                                                        <td class="col-2">{{ $file->sku }}</td>
                                                        <td class="col-2"></td>
                                                        @if ($type == 1)
                                                            <td class="col-1"></td>
                                                        @endif
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @else
                        <h3 class="w-100 text-center my-4"><i class="fa fa-info-circle me-3"></i>Nothing here</h3>
                    @endif
                </tbody>
            </table>
        </div>

        <div class="w-100 pb-3 mt-4 rounded">
            {!! $downloads->onEachSide(3)->links('extra.pagination2') !!}
        </div>
    </main>
@endsection
