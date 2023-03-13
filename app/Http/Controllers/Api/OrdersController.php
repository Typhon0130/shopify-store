<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Jobs\CreateArchive;
use App\Jobs\CreateHoldArchive;
use App\Models\Customsku;
use App\Models\merchant_fee;
use App\Models\Order;
use App\Models\Test;
use App\Models\OrderData;
use App\Models\Plan;
use App\Models\Rejected;
use App\Models\User;
use Carbon\Carbon;
use Response;
use DB;
use Illuminate\Support\Str;

use function PHPUnit\Framework\isNull;

class OrdersController extends Controller
{
    protected $data;
    protected $issue;
    protected $shippingData;
    protected $order_id;
    protected $company_id;
    private $itemsTotalPrice = 0;
    private $itemsTotalWeight = 0;

    // Get new orders
    public function getNewOrders(Request $request)
    {
        // Validate post parameters
        if (!$request->isMethod('post'))
            abort (403, 'This is not a post method!');

        if (!$request->header('user'))
            abort (403, 'Where is the username?');

        if ($request->header('user') != 'myvalentine')
            abort (403, 'Forgot your username?');

        if (!$request->header('pass'))
            abort (403, 'Where is the password?');

        if ($request->header('pass') != '$2y$12$FAzIRc0F1zWAsrsCt3c2Sexs2x7Hd6bpag6su5swjKtysteM5gtOu')
            abort (403, 'Forgot your password?');

	\Illuminate\Support\Facades\Log::info('New orderX: '.file_get_contents("php://input"));

        $this->data = json_decode(file_get_contents("php://input"));

        // Log entire order
        // Test::create(['test' => file_get_contents("php://input")]);
        \Illuminate\Support\Facades\Log::info('New order: '.($this->data->id?$this->data->id:''));

        if (is_null($this->data))
            abort(403, 'Hey, where`s the data?');
            
        if ($this->data->shipping_address->country_code != 'DE' && 
            $this->data->shipping_address->country_code != 'AT' && 
            $this->data->shipping_address->country_code != 'CH')
            abort(403, 'Invalid country code');

        if (!isset($this->data->shopName))
            abort(403, 'Shop name is missing');
        $user = User::where('shop_name', strtolower($this->data->shopName))->first();        
        if(!$user)
            abort(403, 'Unknown company id');		
		$this->company_id = $user->company_id;
		
        $paid_PS = 0;
        $paid_FR = 0;
        $appFee = 0;

        if(strtolower($this->data->tags) == 'broken') $this->issue = 'broken';
        elseif(strtolower($this->data->tags) == 'wrong') $this->issue = 'wrong';
        elseif(strtolower($this->data->tags) == 'dhlissue') $this->issue = 'DHLissue';
        elseif(strtolower($this->data->tags) == 'missingparts') $this->issue = 'Missingparts';
        else $this->data->tags = null; 
        
        // if issue comes from printing company, dont ask to pay for that, i.e. mark as paid
        if(in_array(strtolower($this->data->tags), ['broken', 'wrong', 'missingparts']))
            $paid_PS = 1;

        // if OHM or issued order (does not pay for)
       
        if($this->company_id == 2 || $this->issue){
            $paid_FR = 1;
            $paid_PS = 1;
        }else{
            // instead of calculating percentage and weight fee from whole order, calculate percentage and weight fee by line items with whitelisted sku
            $lineItems = $this->data->line_items;                
            $skuWhitelist = Customsku::where('user_id', $user->id)->where('enabled', 1)->with('sku')->get()->pluck('sku.name')->toArray();
            foreach($lineItems as $lineItem){
                if(in_array($lineItem->sku, $skuWhitelist)){
                    $this->itemsTotalPrice  += $lineItem->price * $lineItem->quantity;
                    $this->itemsTotalWeight += ($lineItem->grams?$lineItem->grams:300) * $lineItem->quantity;
                }
            }

            // if last pay month is current month, Dont know why check (i.e. if not paid for current month dont charge app fee)
            if($user->last_pay_month == date("Y")*12+date("m")){
                // calculate App fee
                $perc = Plan::where('id', $user->current_plan)->first()->percentage;
                $appFee = round($this->itemsTotalPrice * $perc / 100, 2);                
                \Illuminate\Support\Facades\Log::info('itemsTotalPrice: '. $this->itemsTotalPrice);

                // If enough balance
                if($user->balance >= $appFee){
                   // set paid_FR 1 and deduct balance
                    $paid_FR = 1;
                    User::where('id', $user->id)->update(['balance' => $user->balance - $appFee]);
                    // Add to app payment history
                    merchant_fee::create(['user_id' => $user->id, 'order_name' => 'Order fee '.$this->data->name, 'shop_price' => $this->itemsTotalPrice, 'rate' => $perc, 'fee' => -$appFee]);
                }
            }
        }


        $this->shippingData = [
            'shop'          => $this->data->shopName,
            'name'          => $this->data->name,
            'first_name'    => $this->data->shipping_address->first_name,
            'last_name'     => $this->data->shipping_address->last_name,
            'email'         => $this->data->customer->email,
            'address1'      => fixAddress($this->data->shipping_address->address1),
            'address2'      => fixAddress($this->data->shipping_address->address2),
            'phone'         => $this->data->shipping_address->phone,
            'city'          => fixAddress($this->data->shipping_address->city),
            'zip'           => $this->data->shipping_address->zip,
            'province'      => $this->data->shipping_address->province,
            'country'       => $this->data->shipping_address->country,
            'company'       => $this->data->shipping_address->company,
            'shipping_name' => $this->data->shipping_address->name,
            'country_code'  => $this->data->shipping_address->country_code,
            'province_code' => $this->data->shipping_address->province_code,
            'order_id'      => $this->data->id,
            'pending'       => !is_null($this->issue) ? 5 : 0,
            'full_order'    => json_encode($this->data),
            'issue'         => $this->issue,
            'note'          => $this->data->note,
            'total_price'   => $this->company_id == 2 ? $this->data->total_price : $this->itemsTotalPrice,
            'total_weight'  => $this->itemsTotalWeight,
            'weight_fee'    => $this->itemsTotalWeight> 0 ? calculateWeightFee($this->itemsTotalWeight, $this->data->shipping_address->country_code) : 0,
            'paid_FR'       => $paid_FR,/******/
            'paid_PS'       => $paid_PS,/******/
            'app_fee'       => $appFee,/******/
            'company_id'    => $this->company_id, /******/ //$this->data->company_id,//2,
            'created_at'    => $this->data->created_at
        ];
        
        DB::transaction(function() {
           
            // Create order
            $create = Order::firstOrCreate([
                    'order_id'   => $this->data->id,
                    'shop' => $this->data->shopName/******/ //$this->data->company_id
                ],
                $this->shippingData
            );
            
            // Create order data
            if (!$create->wasRecentlyCreated){
                \Illuminate\Support\Facades\Log::info('The entry could not be created. The passed parameters are either not acceptable or the entry already exists.');
                abort(403, 'The entry could not be created. The passed parameters are either not acceptable or the entry already exists.');
            }

            // Create order data
            $job = (new CreateArchive([
                "id"           => $create->id,
                "order_name"   => $this->data->name,
                "properties"   => $this->data->line_items,
                "order_number" => $this->data->order_number,
                "note"         => $this->data->note, 
                "note_attrs"   => $this->data->note_attributes,
                "company_id"   => $this->company_id,
                "shop"         => $this->data->shopName
            ]))
                ->delay(Carbon::now()->addSeconds(20));

            dispatch($job);
        });
       
        return response()->json(["status" => "Success!"]);
    }


    // Get fufulfilled status
    public function setStatus(Request $request)
    {
        if (!$request->isMethod('post'))
            abort(403, 'Wrong method');

        if (!$request->header('user'))
            abort (403, 'Where is the username?');

        if ($request->header('user') != 'myvalentine')
            abort (403, 'Forgot your username?');

        if (!$request->header('pass'))
            abort (403, 'Where is the password?');

        if ($request->header('pass') != '$2y$12$FAzIRc0F1zWAsrsCt3c2Sexs2x7Hd6bpag6su5swjKtysteM5gtOu')
            abort (403, 'Forgot your password?');

        $this->data = json_decode(file_get_contents("php://input"));

        if (is_null($this->data))
            abort(403, 'Hey, where`s the data?');

        if (!isset($this->data->company_id))
            abort(403, 'Hey, where`s the company ID?');

        if (!is_numeric($this->data->company_id))
            abort(403, 'Look, this is not the correct company ID');

        if (!isset($this->data->orderID))
            abort(403, 'Hey, where`s the order ID?');

        if (!is_numeric($this->data->orderID))
            abort(403, 'Look, this is not the correct order ID');

        Order::where("order_id", "=", $this->data->orderID)->update(["status" => $this->data->fulfillmentStatus]);
    }


    public function recoverOrder(Request $request){
        \Illuminate\Support\Facades\Log::info('Recover Order: ' . file_get_contents("php://input"));

        // Validate post parameters
        if (!$request->isMethod('post'))
            abort (403, 'This is not a post method!');

        if (!$request->header('user'))
            abort (403, 'Where is the username?');

        if ($request->header('user') != 'myvalentine')
            abort (403, 'Forgot your username?');

        if (!$request->header('pass'))
            abort (403, 'Where is the password?');

        if ($request->header('pass') != '$2y$12$FAzIRc0F1zWAsrsCt3c2Sexs2x7Hd6bpag6su5swjKtysteM5gtOu')
            abort (403, 'Forgot your password?');

        $data = json_decode(file_get_contents("php://input"));

        $orderName      = $data->name;
        $orderNumber    = $data->order_number;
        $noteAttributes = $data->note_attributes;

        $order = Order::where('name', $orderName)->first();
        if($order){
            $lineItems = $data->line_items;

            foreach ($lineItems as $key => $data)
            {
                $images = [];
                foreach ($data->properties as $val)
                {
                    // Teeinblu item
                    if ($val->name == 'customization_id'){
                        $customization_id = $val->value;
                        foreach ($noteAttributes as $k => $note_attr)
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
                    for($i=0; $i<count($images); $i++){
                        $filename = changeImageName($images[$i], $orderNumber, $key, $i)["image"];
                        $url = changeImageName($images[$i], $orderNumber, $key, $i)["url"];
                        $this->files[$key]['image'.($i>0?"_".($i+1):'')] = $filename; 

                        $imageData = file_get_contents($url);
                        \Image::make($imageData)
                            ->save(public_path('archive/' . $orderName . '/' . $filename))
                            ->resize(null, 60, function ($constraint) {$constraint->aspectRatio();})
                            ->save(public_path('uploads/thumbs/' . $filename));
                        
                        $column = 'image'.($i>0?"_".($i+1):'');
                        OrderData::where(['order_id' => $order->id, 'line_item_id' => $data->id])->update([$column => $filename]);
                    }
                }
            }
            return response()->json(["status" => "Success!"]);
        }
        return response()->json(["status" => "Error!"]);
    }


}
