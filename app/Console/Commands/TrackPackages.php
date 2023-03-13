<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TrackPackages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'track:packages';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Track sent DHL packages';

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
        $currentOrders = \App\Models\Order::whereDate('created_at', '>=', \Carbon\Carbon::now()->subDays(120))
										  ->whereNotNull('shipment_order')
										  ->whereNull('delivered_at')
										  ->orderBy('created_at', 'DESC')	
										  ->get();
        
        Log::info('Checking '.count($currentOrders). 'orders...');   
	
        foreach($currentOrders as $co){
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, env('DHL_TRACKING_URL').$co->shipment_order);
            $headers = ['DHL-API-Key:'.env('DHL_TRACKING_APIKEY')];
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $output = curl_exec($curl);
            curl_close($curl);
            
            $res=json_decode($output);
            if (isset($res->shipments[0]->status->statusCode)){
                if($res->shipments[0]->status->statusCode=='delivered'){				
                    \App\Models\Order::where('id', $co->id)->update(['delivery_status' => $res->shipments[0]->status->statusCode,
                                                                    'delivered_at' => $res->shipments[0]->status->timestamp]);				
                    $co->statusCode = $res->shipments[0]->status->statusCode;
                    $co->delivered_at = $res->shipments[0]->status->timestamp;
                    Log::info('Order: '.$co->shipment_order.' delivered');
                }else{
                    \App\Models\Order::where('id', $co->id)->update(['delivery_status' => $res->shipments[0]->status->statusCode,
                                                                    'delivery_details' => strip_tags($res->shipments[0]->status->description)]);
                    $co->statusCode = $res->shipments[0]->status->statusCode;
                    $co->details = strip_tags($res->shipments[0]->status->description);
                }
            }
            sleep(2);
        }
    }
}
