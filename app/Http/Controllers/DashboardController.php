<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use \App\Models\Order;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $orders = json_decode(file_get_contents(resource_path('files/dashboard.json')));

        // var_dump($orders);exit;

        return view('welcome', [
            'title'  => 'Dashboard',
            'class'  => 'dashboard',
            'orders' => $orders
        ]);
    }

    public function getData(){
        
        $from = date("Y-m-d", strtotime("-7 days"));
        $to   = date("Y-m-d");
        $fromPrev = date("Y-m-d", strtotime("-15 days"));
        $toPrev   = date("Y-m-d", strtotime("-8 days"));

        $collection = Order::with('files')
                        ->whereDate('created_at', ">=", $from)
                        ->whereDate('created_at', "<=", $to)
                        ->orderBy('created_at', 'asc');

        if(Auth::user()->company_id !== 0)
            $collection = $collection->where('company_id', Auth::user()->company_id);
        // else
        //     $collection = $collection->where('paid_FR', 1)->where('paid_PS', 1); // Print service

        $orders = $collection->get();

        $new         = count($orders->where('pending', 0));
        $pending     = count($orders->where('pending', 1));
        $shipped     = count($orders->where('pending', 2));
        $unfulfilled = count($orders->where('pending', 0))+count($orders->where('pending', 1));
        $type = 'dashboard';
        $total = $new + $pending + $shipped;

        $prev_ord = Order::whereDate('created_at', ">=", $fromPrev)
                         ->whereDate('created_at', "<=", $toPrev);
        if(Auth::user()->company_id !== 0)
            $prev_ord = $prev_ord->where('company_id', Auth::user()->company_id);
        $prev_orders = $prev_ord->get();

        $prev_new         = count($prev_orders->where('pending', 0));
        $prev_pending     = count($prev_orders->where('pending', 1));
        $prev_shipped     = count($prev_orders->where('pending', 2));
        $prev_unfulfilled = count($prev_orders->where('pending', 0))+count($prev_orders->where('pending', 1));
        
        // echo 'New: '.$new . '<br>';
        // echo 'pending: '.$pending . '<br>';
        // echo 'shipped: '.$shipped . '<br>';
        // echo 'unfulfilled: '.$unfulfilled . '<br><br><br>';

        // echo 'New: '.$prev_new . '<br>';
        // echo 'pending: '.$prev_pending . '<br>';
        // echo 'shipped: '.$prev_shipped . '<br>';
        // echo 'unfulfilled: '.$prev_unfulfilled . '<br>';

        $data['new'] = [
            'current' => $new,
            'ratio' => $prev_new>0 ? round(count($orders)/count($prev_orders)*100) : 0,
            'color' => getRatioColor(count($orders), count($prev_orders)),
            'icon'  => getRatioIcon(count($orders), count($prev_orders))
        ];
        $data['pending'] = [
            'current' => $pending,
            // 'ratio' => $prev_pending>0 ? round($pending/$prev_pending*100) : 0,
            // 'color' => getRatioColor($pending, $prev_pending),
            // 'icon' => getRatioIcon($pending, $prev_pending)
        ];
        $data['shipped'] = [
            'current' => $shipped,
            'ratio'   => $total>0?round($shipped/$total*100):0,
            'color'   => 'orange',
            // 'ratio' => $prev_shipped>0 ? round($shipped/$prev_shipped*100) : 0,
            // 'color' => getRatioColor($shipped, $prev_shipped),
            // 'icon' => getRatioIcon($shipped, $prev_shipped)
        ];
        $data['unfulfilled'] = [
            'current' => $unfulfilled,
            'ratio'   => $total>0?round($unfulfilled/$total*100):0,
            'color'   => 'orange',
            // 'ratio' => $prev_unfulfilled>0 ? round($unfulfilled/$prev_unfulfilled*100) : 0,
            // 'icon' => getRatioIcon($unfulfilled, $prev_unfulfilled)
        ];
        $orders = $collection->paginate(30);
        return view('welcome', compact(['orders', 'data', 'type']));
    }



    public function ajaxGetData(Request $request){
        if($request->ajax()){
            if($request->from && $request->to && $request->from <= $request->to){
                $from = $request->from;
                $to = $request->to;
                $diff = Carbon::parse($from)->diffInDays($to);

                $collection = Order::with('files')
                                    ->whereDate('created_at', ">=", $from)
                                    ->whereDate('created_at', "<=", $to);
                if(Auth::user()->company_id !== 0)
                    $collection = $collection->where('company_id', Auth::user()->company_id);
            
                $orders = $collection->get();

                $new         = count($orders->where('pending', 0));
                $pending     = count($orders->where('pending', 1));
                $shipped     = count($orders->where('pending', 2));
                $unfulfilled = count($orders->where('pending', 0))+count($orders->where('pending', 1));
                $total = $new + $pending + $shipped;
        
                $prev_ord = Order::whereDate('created_at', ">=", Carbon::parse($from)->subDays($diff+1)->toDateString())
                                 ->whereDate('created_at', "<=", Carbon::parse($from)->subDays(1)->toDateString());
                if(Auth::user()->company_id !== 0)
                    $prev_ord = $prev_ord->where('company_id', Auth::user()->company_id);
                $prev_orders = $prev_ord->get();

        
                $prev_new         = count($prev_orders->where('pending', 0));
                // $prev_pending     = count($prev_orders->where('pending', 1));
                // $prev_shipped     = count($prev_orders->where('pending', 2));
                // $prev_unfulfilled = count($prev_orders->where('pending', 0))+count($prev_orders->where('pending', 1));

                $data['new'] = [
                    'current' => $new,
                    'ratio' => $prev_new>0 ? round(count($orders)/count($prev_orders)*100) : 0,
                    'color' => getRatioColor(count($orders), count($prev_orders)),
                    'icon'  => getRatioIcon(count($orders), count($prev_orders))
                ];
                $data['pending'] = [
                    'current' => $pending,
                    // 'ratio' => $prev_pending>0 ? round($pending/$prev_pending*100) : 0,
                    // 'color' => getRatioColor($pending, $prev_pending),
                    // 'icon' => getRatioIcon($pending, $prev_pending)
                ];
                $data['shipped'] = [
                    'current' => $shipped,
                    'ratio'   => ($shipped>0&&$total>0)?round($shipped/$total*100):0,
                    'color'   => 'orange',
                    // 'ratio' => $prev_shipped>0 ? round($shipped/$prev_shipped*100) : 0,
                    // 'color' => getRatioColor($shipped, $prev_shipped),
                    // 'icon' => getRatioIcon($shipped, $prev_shipped)
                ];
                $data['unfulfilled'] = [
                    'current' => $unfulfilled,
                    'ratio'   => ($unfulfilled>0&&$total>0)?round($unfulfilled/$total*100):0,
                    'color'   => 'orange',
                    // 'ratio' => $prev_unfulfilled>0 ? round($unfulfilled/$prev_unfulfilled*100) : 0,
                    // 'icon' => getRatioIcon($unfulfilled, $prev_unfulfilled)
                ];
            }

            $orders = $collection->paginate(30);
            return ['view' => view('table', ['orders' => $orders, 'type' => 'dashboard'])->render(), 'data' => $data];
        }
    }


    

    
}
