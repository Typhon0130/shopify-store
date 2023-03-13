<?php

namespace App\Jobs;

use Log;
use File;
use App\Models\Order;
use App\Models\OrderData;
use App\Models\Sku;
use Illuminate\Bus\Queueable;
use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PhpParser\Node\Stmt\TryCatch;

class CreateArchive implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;
    protected $files;
    protected $input;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($input)
    {
        $this->input = $input;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        
        $hold = false;
        $orderDataCount = 0;
        foreach ($this->input['properties'] as $key => $data)
        {
            
            // get data from skus table for current price and print_price 
            $skuData        = Sku::where('name', str_replace('-HOLD', '', $data->sku))->first();
            // get SKU price/print_price first from custom sku table
            $user_id        = \App\Models\User::where('shop_name', $this->input['shop'])->first()->id;
            $customSkuData  = null;
            $price          = null;
            $print_price    = null;
            $orderName      = $this->input['order_number']; // XXXXXX
            $fullorderName  = $this->input['order_name']; // #omvXXXXXX
            if($skuData)
                // $customSkuData = \App\Models\Customsku::where('user_id', $user_id)->where('sku_id', $skuData->id)->where('enabled', 1)->first();
                $customSkuData = \App\Models\Customsku::where('sku_id', $skuData->id)->where('enabled', 1)->first();
            
            if($this->input['company_id'] == 2){
                $price          = $customSkuData ? $customSkuData->price       : ($skuData ? $skuData->price       : null);
                $print_price    = $customSkuData ? $customSkuData->print_price : ($skuData ? $skuData->print_price : null);
                $orderDataCount++;
            }else{
                // Non omv merchant
                if($customSkuData){
                    // SKU data found in custom_sku folder
                    $price       = $customSkuData->price;
                    $print_price = $customSkuData->print_price;
                    $orderDataCount++;
                }else{
                    continue;
                }
            }
            
            //vendor
            $vendor = strtolower($data->vendor) == 'teeinblue' ? 'TB' : 'OMV';
            
            // Data array (order-data)
            $this->files[$key] = [
                "product_id"    => $data->product_id,
                "sku"           => $data->sku,
                "grams"         => ($data->grams == null || $data->grams == 0) ? 300 : $data->grams,
                "quantity"      => $data->fulfillable_quantity != 0 ? $data->fulfillable_quantity : 1,
                "title"         => $data->title,
                "variant_title" => $data->variant_title,
                "vendor"        => $vendor,
                "order_id"      => $this->input['id'],
                "hold"          => strpos(strtoupper($data->sku), "HOLD") !== false ? $key : null,
                "price"         => $price,
                "print_price"   => $print_price,
                "line_item_id"  => $data->id
            ];

            // Create directory for order
            $this->path = public_path().'/archive/' . $this->input['order_name'];
            File::isDirectory($this->path) or File::makeDirectory($this->path, 0755, true, true);
           
            if(strpos(strtoupper($data->sku), "HOLD") !== false){
                $hold = true;
                //$this->files[$key]["image"] = $key; //hold image index
            }else{
                // normal order, non HOLD                
                $images = [];
                foreach ($data->properties as $val)
                {
                    // Teeinblu item
                    if ($val->name == 'customization_id'){
                        $customization_id = $val->value;
                        foreach ($this->input['note_attrs'] as $k => $note_attr)
                        {
                            if (strpos($note_attr->name, trim($customization_id)) !== false) {
                                array_push($images, $note_attr->value);
                            }
                        }
                    }
                    // PP item
                    if ($val->name == '_design__Preview')
                    {
                        array_push($images, !is_null(json_decode($val->value)) ? json_decode($val->value)[1] : $val->value);
                    }                    
                }
                // No customization_id and _design__Preview keys found
                if(!count($images)){
                    foreach ($data->properties as $val)
                    {
                        if ($val->name == '_Preview')
                        {
                            array_push($images, !is_null(json_decode($val->value)) ? json_decode($val->value)[1] : $val->value);
                        }
                    }
                }
                if(count($images)){
                    for($i=0; $i<count($images); $i++)
                        $this->generateImage($images[$i], $orderName, $fullorderName, $key, $i);
                }
            }
            // Create record
            $orderData = OrderData::create($this->files[$key]);

            // // Add sku if new
            // $sku = Sku::firstOrCreate([
            //     'product_id' => $data->product_id
            // ], [
            //     'product_id' => $data->product_id
            // ]);

            // // If sku was not created set cost and sell price from sku to order-data 
            // if (!$sku->wasRecentlyCreated){
            //     OrderData::where('id', $orderData->id)->update(['cost' => $sku->cost, 'sell_price' => $sku->sell_price]);
            // }

        }
        // Set pending to 6 (HOLD)
        if($hold) 
            Order::where('id', $this->input['id'])->update(['pending' => 6]);
        if(!$orderDataCount)
            Order::where('id', $this->input['id'])->delete();
    }




    private function generateImage($image, $orderName, $fullorderName, $key, $side){
        $filename = changeImageName($image, $orderName, $key, $side)["image"];
        $url = changeImageName($image, $orderName, $key, $side)["url"];
        $this->files[$key]['image'.($side>0?"_".($side+1):'')] = $filename; // Add '_i' if multiple sided
        // $this->files[$key]['img_url'] = $url; // we dont saveimage url
        // Download order data image

        $imageData = file_get_contents($url);
        \Image::make($imageData)->save(public_path('archive/' . $fullorderName . '/' . $filename))
        ->resize(null, 60, function ($constraint) {
            $constraint->aspectRatio();
        })
        ->save(public_path('uploads/thumbs/' . $filename));

        /*
        \Image::make($url)
            ->save(public_path('archive/' . $fullorderName . '/' . $filename))
            ->resize(null, 60, function ($constraint) {
                $constraint->aspectRatio();
            })
            ->save(public_path('uploads/thumbs/' . $filename));*/
    }
}