<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaginationController extends Controller
{
    function index()
    {
        $data = \App\Models\Order::whereNotNull('issue')->paginate(10);
     return view('pagination', compact('data'));
    }

    function fetch_data(Request $request)
    {
     if($request->ajax())
     {
        $data = \App\Models\Order::whereNotNull('issue')->paginate(10);
      return view('pagination_data', compact('data'))->render();
     }
    }
}
