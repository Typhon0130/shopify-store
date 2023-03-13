<?php

namespace App\Http\Controllers;

use DB;
use Response;
use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Http\Request;

class ReportController extends Controller
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
    public function index(Request $request)
    {
        $count = 0;
        $data  = [];

        $from = !isset($request->start)
            ? fromTo($request->date, $request->from, $request->to)['from']
            : $request->start;
        $to = !isset($request->end)
            ? fromTo($request->date, $request->from, $request->to)['to']
            : $request->end;

        $orders = Order::with('files')
            ->where('pending', '!=', 3)
            ->whereDate('created_at', ">=", $from)
            ->whereDate('created_at', "<=", $to)
            ->orderBy('created_at', 'asc')
            ->get();

        $data['new']         = 0;
        $data['pending']     = 0;
        $data['shipped']     = 0;
        $data['unfulfilled'] = 0;
        $data['html']        = null;

        for ($i = 0; $i < count($orders); $i++)
        {
            if ($orders[$i]->pending == 0)
            {
                $data['new']++;
            }
            else if ($orders[$i]->pending == 1)
            {
                $data['pending']++;
            }
            else
            {
                if (!is_null($orders[$i]->status))
                {
                    $data['unfulfilled']++;
                }

                $data['shipped']++;
            }


            $data["html"] .=
            '<tbody>
                <tr>
                    <td class="d-flex col-2 omv-class-'. $orders[$i]->pending .'">
                        '. $orders[$i]->name .'
                    </td>
                    <td class="col-2 accordion-toggle" data-toggle="collapse" data-target="#collapse_'. $i .'">
                        <b class="darkBlue">Expand Files</b><i class="fa fa-caret-down ml-1"></i>
                    </td>
                    <td class="col-2">
                        '. count($orders[$i]->files) .'
                    </td>
                    <td class="col-2">
                        '. $orders[$i]->files[0]->sku .'
                    </td>';
                    if (!is_null($orders[$i]->issue))
                    {
                        $data["html"] .= '<td class="col-1"><span class="badge badge-info">'. $orders[$i]->issue .'</span></td>';
                    }
                    else
                    {
                        $data["html"] .= '<td class="col-1"></td>';
                    }
                    $data["html"] .= 
                    '<td class="col-3">'. Carbon::parse($orders[$i]->created_at)->format("Y-m-d H:i:s") .'</td>

                    <tr>
                        <td colspan="8" class="hiddenRow">
                            <div class="collapse" id="collapse_'. $i .'">
                                <table class="table table-condensed">
                                    <tbody>';
                                        foreach ($orders[$i]->files as $file){
                                            $data['html'] .= '
                                            <tr>
                                                <td class="col-2"></td>
                                                <td class="col-2 mb-4">
                                                    <img src="uploads/thumbs/'. $file->image .'">
                                                </td>
                                                <td class="col-2"></td>
                                                <td class="col-2"></td>
                                                <td class="col-1"></td>
                                                <td class="col-3"></td>
                                            </tr>';
                                        }
                    
                    $data["html"] .=                     
                                    '</tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                </tr>
            </tbody>';

        }

        return $data;
        
    }


    public function index2(Request $request)
    {
        $count = 0;
        $data  = [];

        $from = !isset($request->start)
            ? fromTo($request->date, $request->from, $request->to)['from']
            : $request->start;
        $to = !isset($request->end)
            ? fromTo($request->date, $request->from, $request->to)['to']
            : $request->end;

        $orders = Order::with('files')
            ->where('pending', '!=', 3)
            ->whereDate('created_at', ">=", $from)
            ->whereDate('created_at', "<=", $to)
            ->orderBy('created_at', 'asc')
            ->get();

        $data['new']         = 0;
        $data['pending']     = 0;
        $data['shipped']     = 0;
        $data['unfulfilled'] = 0;
        $data['html']        = null;

        for ($i = 0; $i < count($orders); $i++)
        {
            if ($orders[$i]->pending == 0)
            {
                $data['new']++;
            }
            else if ($orders[$i]->pending == 1)
            {
                $data['pending']++;
            }
            else
            {
                if (!is_null($orders[$i]->status))
                {
                    $data['unfulfilled']++;
                }

                $data['shipped']++;
            }

            
            $data["html"] .=
            '<tbody>
                <tr>
                    <td class="d-flex col-2 omv-class-'. $orders[$i]->pending .'">
                        '. $orders[$i]->name .'
                    </td>
                    <td class="col-2 accordion-toggle" data-toggle="collapse" data-target="#collapse_'. $i .'">
                        <b class="darkBlue">Expand Files</b><i class="fa fa-caret-down ml-1"></i>
                    </td>
                    <td class="col-2">
                        '. count($orders[$i]->files) .'
                    </td>
                    <td class="col-2">
                        '. $orders[$i]->files[0]->sku .'
                    </td>';
                    if (!is_null($orders[$i]->issue))
                    {
                        $data["html"] .= '<td class="col-1"><span class="badge badge-info">'. $orders[$i]->issue .'</span></td>';
                    }
                    else
                    {
                        $data["html"] .= '<td class="col-1"></td>';
                    }
                    $data["html"] .= 
                    '<td class="col-3">'. Carbon::parse($orders[$i]->created_at)->format("Y-m-d H:i:s") .'</td>

                    <tr>
                        <td colspan="8" class="hiddenRow">
                            <div class="collapse" id="collapse_'. $i .'">
                                <table class="table table-condensed">
                                    <tbody>';
                                        foreach ($orders[$i]->files as $file){
                                            $data['html'] .= '
                                            <tr>
                                                <td class="col-2"></td>
                                                <td class="col-2 mb-4">
                                                    <img src="uploads/thumbs/'. $file->image .'">
                                                </td>
                                                <td class="col-2"></td>
                                                <td class="col-2"></td>
                                                <td class="col-1"></td>
                                                <td class="col-3"></td>
                                            </tr>';
                                        }
                    
                    $data["html"] .=                     
                                    '</tbody>
                                </table>
                            </div>
                        </td>
                    </tr>
                </tr>
            </tbody>';

        }

        return $data;

        /*if ($orders->isEmpty())

            return response()->json(['status' => null]);*/

        /*foreach($orders as $key => $order)
        {
            $html .= '<li class="list-group-item mb-4 border-0 '. ($order->pending == 1 ? 'dhl-class' : 'omv-class') .'">
                <div class="row">
                    <div class="col-2">'. $order->name .'</div>
                </div>
            </li>';
        }

        dd($html);*/

        /*foreach($orders as $key => $order)
        {
            if ($order->created_at >= date("Y-m-d", strtotime("-8 days", strtotime($this->to))))
            {
                $this->html .= '<li class="list-group-item mb-4 border-0 '. ($order->pending == 1 ? 'dhl-class' : 'omv-class') .'">
                    <div class="row">
                        <div class="col-2">'. $order->name .'</div>
                        <div class="col-2" data-bs-toggle="collapse" href="#collapse_'. $key .'" role="button" aria-expanded="false" aria-controls="collapse_'. $key .'">
                            <b class="darkBlue">Expand Files</b><i class="fa fa-caret-down ms-2"></i>
                        </div>
                        <div class="col-2">'. count($order->files) .'</div>
                        <div class="col-2">'. $order->sku .'</div>
                        <div class="col-4">'. Carbon::parse($order->created_at)->format("Y-m-d H:i:s") .'</div>

                        <div class="col-12 collapse pt-4 mt-4" id="collapse_'. $key .'">
                            <div class="row">';
                                foreach($order->files as $file)
                                {
                                    $this->html .= '<div class="d-flex align-items-center col-2 mb-4 offset-2">
                                        <img src="uploads/thumbs/'. $file->image .'">
                                    </div>';
                                }
                            $this->html .= '</div>
                        </div>

                    </div>
                </li>';

                // Pending DHL count
                $order->pending != 2 ?: $this->countDhl++;

                // All orders count
                $this->countOmv++;
            }
            else
            {
                // Pending DHL count from before week
                $order->pending != 2 ?: $this->countBeforeWeekDhl++;

                // All orders count from before week
                $this->countBeforeWeekOmv++;
            }
        }*/


        // Get last and before last week orders for DHL
        /*if ($this->countBeforeWeekDhl < $this->countDhl)
        {
            $this->equality   = 'more';
            $this->dhlPercent = ($this->countDhl / $this->countBeforeWeekDhl * 100) - 100;
        }
        else
        {
            $this->equality   = 'smaller';
            $this->dhlPercent = ($this->countBeforeWeekDhl / $this->countDhl * 100) - 100;
        }


        // Get last and before last week orders for OMV
        if ($this->countBeforeWeekOmv < $this->countOmv)
        {
            $this->equalityOmv = 'more';
            $this->omvPercent  = ($this->countOmv / $this->countBeforeWeekOmv * 100) - 100;
        }
        else
        {
            $this->equalityOmv = 'smaller';
            $this->omvPercent  = ($this->countBeforeWeekOmv / $this->countOmv * 100) - 100;
        }

        return response()->json([
            'equality'   => $this->equality,
            'equality'   => $this->equalityOmv,
            'countDhl'   => $this->countDhl,
            'dhlPercent' => round($this->dhlPercent),
            'countOmv'   => $this->countOmv,
            'omvPercent' => round($this->omvPercent),
            'orders'     => $this->html
        ]);*/
    }


    // Reports
    public function reports($type)
    {
        $this->to   = date("Y-m-d");
        $this->from = date('Y-m-d', strtotime("-3 week"));

        // Get last 3 week orders
        $orders = Order::select('created_at')
            ->whereDate('created_at', ">=", $this->from)
            ->whereDate('created_at', "<=", $this->to)
            ->orderBy('created_at')
            ->get()
            ->groupBy(function($date) {
                return Carbon::parse($date->created_at)->format('W');
            });

        
        // My code
        // $request = new Request();
        // $request->replace(['year' => date("Y"), 'month' => date("m")-1]);
        // $ajaxController = new \App\Http\Controllers\AjaxController;
        // $reports = $ajaxController->getReportData($request);
        return view('reports', [
            'title'   => 'Reports',
            'class'   => 'reports',
            'orders'  => $orders,
            // 'reports' => json_decode($reports)
        ]);
    }


    public function test(Request $request){
        $from = date("2021-10-25");
        $to = date("Y-m-d", strtotime("-7 days"));
        $orders = Order::with('files')
            ->where('pending', '!=', 3)
            ->whereDate('created_at', ">=", $from)
            ->whereDate('created_at', "<=", $to)
            ->orderBy('created_at', 'asc')
            ->get();

        print_r($orders);
    }
}