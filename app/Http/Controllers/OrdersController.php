<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
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

    
    public function index($type, $where = null)
    {
        $orders = Order::where('pending', '=', config('app.custom.' . $type . '.type'))
            ->with('files')
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc');

        if(Auth::user()->company_id !== 0)
            $orders = $orders->where('company_id', Auth::user()->company_id); // Merchant
        else
            $orders = $orders->where('paid_FR', 1)->where('paid_PS', 1); // Print service

        if ($where == 'issued'){
            $result = $orders->whereNotNull('issue');
        }else if ($where == 'orders'){
            $result = $orders->whereNull('issue')->whereNull('call_Dhl_error');
        }else if ($where == 'dhl-rejected'){
            $result = $orders->select('*', 'call_Dhl_error as issue')->whereNotNull('call_Dhl_error');
        }else if ($where == 'in-transit'){
            $result = $orders->where('delivery_status', 'transit')->orWhere('delivery_status', 'pre-transit');
        }else if ($where == 'failed'){
            $result = $orders->where('delivery_status', 'failure');
        }else if ($where == 'delivered'){
            $result = $orders->whereNotNull('delivered_at');
        }else{
            $result = $orders;
        }

        $orders = $result->paginate(30);

        foreach($orders as $order){
            $psum = 0;
            if(!$order->paid_PS && !in_array(strtolower($order->issue), ['broken', 'wrong', 'missingparts'])){
                foreach($order->files as $file){
                    $psum += $file->price * $file->quantity;
                    $psum += $file->print_price * $file->quantity;
                }
            }
            $order->psum = $psum + $order->weight_fee;
        }

        if ($type == 'pending' && $where == null)
            foreach($orders as $order) 
                if($order->call_Dhl_error != null) 
                    $order->issue = $order->call_Dhl_error;        
        

        return view('orders', [
            'title'  => str_replace(["-", "pending"], [" ", ""], ucfirst($type) . ($type != 'trash' ? ' Orders' : null)),
            'class'  => 'orders',
            'orders' => $orders,
            'type'   => config('app.custom.' . $type . '.type'),
        ]);
    }


    public function ajaxorders($type, $where = null, Request $request){
        if($request->ajax()){
            $orders = Order::where('pending', '=', config('app.custom.' . $type . '.type'))
            ->with('files')
            ->orderBy('updated_at', 'desc')
            ->orderBy('created_at', 'desc');

            if(Auth::user()->company_id !== 0)
                $orders = $orders->where('company_id', Auth::user()->company_id);
            else                
                $orders = $orders->where('paid_FR', 1)->where('paid_PS', 1);

            if ($where == 'issued'){
                $result = $orders->whereNotNull('issue');
            }else if ($where == 'orders'){
                $result = $orders->whereNull('issue')->whereNull('call_Dhl_error');
            }else if ($where == 'dhl-rejected'){
                $result = $orders->select('*', 'call_Dhl_error as issue')->whereNotNull('call_Dhl_error');
            }else if ($where == 'in-transit'){
                $result = $orders->where('delivery_status', 'transit')->orWhere('delivery_status', 'pre-transit');
            }else if ($where == 'failed'){
                $result = $orders->where('delivery_status', 'failure');
            }else if ($where == 'delivered'){
                $result = $orders->whereNotNull('delivered_at');
            }else{
                $result = $orders;
            }

            if($request->from && $request->to && $request->from <= $request->to){
                if($type == 'dhl-pending'){
                    $result = $result->whereDate('calledDHL_at', '>=', $request->from);
                    $result = $result->whereDate('calledDHL_at', '<=', $request->to);
                }else{
                    $result = $result->whereDate('created_at', '>=', $request->from);
                    $result = $result->whereDate('created_at', '<=', $request->to);
                }
            }

            $orders = $result->paginate(30);

            foreach($orders as $order){
                $psum = 0;
                if(!$order->paid_PS && !$order->issue){
                    foreach($order->files as $file){
                        $psum += $file->price * $file->quantity;
                        $psum += $file->print_price * $file->quantity;
                    }
                }
                $order->psum = $psum + $order->weight_fee;
            }
            
            if ($type == 'pending' && $where == null)
                foreach($orders as $order) 
                    if($order->call_Dhl_error != null) 
                        $order->issue = $order->call_Dhl_error;

            return view('tables.'.$type, [
                'title'  => str_replace(["-", "pending"], [" ", ""], ucfirst($type) . ($type != 'trash' ? ' Orders' : null)),
                'class'  => 'orders',
                'orders' => $orders,
                'type'   => config('app.custom.' . $type . '.type'),
            ])->render();
        }
        
    }
}
