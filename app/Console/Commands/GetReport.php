<?php

namespace App\Console\Commands;


use File;
use App\Models\OrderData;
use Illuminate\Http\Request;
use App\Http\Controllers\ReportController;



use DB;
use Carbon\Carbon;
use App\Models\Order;
use Illuminate\Console\Command;

class GetReport extends Command
{
    protected $to;
    protected $from;
    protected $html;
    protected $equality;
    protected $countDhl = 1;
    protected $countOmv = 1;
    protected $dhlPercent;
    protected $omvPercent;
    protected $equalityOmv;
    protected $countBeforeWeekDhl = 1;
    protected $countBeforeWeekOmv = 1;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get:report';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get orders report';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $report  = new ReportController();
        $request = new Request();
        $request->merge(["start" => date("Y-m-d", strtotime("-7 days"))]);
        $request->merge(["end" => date("Y-m-d")]);

        $data = $report->index($request);

        // Write data to dashboard JSON
        file_put_contents(resource_path('files/dashboard.json'), json_encode($data));

        /*$i = 0;
        $a = Order::select("id", "full_order")->get();

        foreach ($a as $b)
        {
            $path = public_path().'/archive/' . json_decode($b->full_order)->name;
            File::isDirectory($path) or File::makeDirectory($path, 0755, true, true);

            foreach (json_decode($b->full_order)->line_items as $f)
            {
                foreach ($f->properties as $key => $p)
                {
                    if ($p->name == '_design__Preview')
                    {
                        $image = changeImageName($p->value, json_decode($b->full_order)->order_number, $i)["image"];
                        $url = changeImageName($p->value, json_decode($b->full_order)->order_number, $i)["url"];

                        \Image::make(changeImageName($p->value, json_decode($b->full_order)->order_number, $i)["url"])
                        ->save(public_path('archive/' . json_decode($b->full_order)->name . '/' . changeImageName($p->value, json_decode($b->full_order)->order_number, $i)["image"]))
                        ->resize(null, 60, function ($constraint) {
                            $constraint->aspectRatio();
                        })
                        ->save(public_path('uploads/thumbs/' . changeImageName($p->value, json_decode($b->full_order)->order_number, $i)["image"]));

                        $i++;
                    }
                }
            }

            $i = 0;
        }

        dd("done");*/

        /*$this->to   = date("Y-m-d");
        $this->from = date('Y-m-d', strtotime("-2 week"));

        $orders = Order::with('files')
            ->where('pending', '!=', 3)
            ->whereDate('created_at', ">=", $this->from)
            ->whereDate('created_at', "<=", $this->to)
            ->orderBy('created_at', 'desc')
            ->limit(30)
            ->get();

        if ($orders->isEmpty())

            return response()->json(['status' => null]);

        foreach($orders as $key => $order)
        {
            if ($order->created_at >= date("Y-m-d", strtotime("-8 days", strtotime($this->to))))
            {
                if ($key <= 20)
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
                }

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
        }

        // Get last and before last week orders for DHL
        if ($this->countBeforeWeekDhl < $this->countDhl)
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


        // Generate dashboard data
        $this->weekData = [
            "caretDhl"   => $this->equality == 'more' ? 'up' : 'down',
            "classDhl"   => $this->equality == 'more' ? 'orange' : 'red',
            "countDhl"   => $this->countDhl,
            "dhlPercent" => round($this->dhlPercent),
            "caretOmv"   => $this->equalityOmv == 'more' ? 'up' : 'down',
            "classOmv"   => $this->equalityOmv == 'more' ? 'green' : 'red',
            "countOmv"   => $this->countOmv,
            "omvPercent" => round($this->omvPercent),
            "html"       => $this->html
        ];


        // Create json file for dashboard
        file_put_contents(resource_path('files/dashboard.json'), serialize($this->weekData));*/
    }
}
