<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderData;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    protected $to;
    protected $from;
    protected $find;
    protected $part;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->from = "2021-05-23";
        $this->to   = date("Y-m-d");
    }


    // Move to
    public function index(Request $request)
    {
        if (isset($request->find))
        {
            if (!isset($request->type))
            {
                $this->find = $request->find[0] == '#'
                    ? $request->find
                    : '#' . $request->find;

                $orders = Order::where('name', '=', $this->find)->get();

                if (count($orders) > 0)
                {
                    $this->part = "search-number";

                    $files = Order::with('files')
                        ->where('name', '=', $this->find)
                        ->first();

                    $type = $files->pending;
                }
                else
                {
                    $type = null;
                    $this->part = "search-sku";
                    $this->find = str_replace("#", "", $this->find);

                    $files = OrderData::with('parent')
                        ->where('sku', '=', $this->find)
                        ->paginate(30);
                }
            }
            else
            {
                $this->from = isset($request->from) ? $request->from : $this->from;

                dd($this->from);
            }

            return view('search', [
                'title'  => 'Search',
                'class'  => 'search',
                'orders' => $files,
                'find'   => $this->find,
                'type'   => $type,
                'part'   => $this->part
            ]);
        }
        else
        {
            echo '<a href="'. route('home')  .'">Session time ended, go to home page</a>';
        }
    }

    public function ajaxsearch(Request $request){
        if($request->ajax()){
            $files = OrderData::with('parent')->where('sku', '=', $request->find);

            if($request->from && $request->to && $request->from <= $request->to){
                $from = $request->from;
                $to = $request->to;
                $files = $files->whereHas('parent', function($q) use ($from){
                    return $q->where('created_at', '>=', $from.' 00:00:00');
                });       
                $files = $files->whereHas('parent', function($q) use ($to){
                    return $q->where('created_at', '<=', $to.' 23:59:59');
                });
            }
           
            return view('partials.search-sku', [
                'orders' => $files->paginate(30),
            ])->render();
        }        
    }




}
