<?php

namespace App\Http\Controllers;

use App\Models\Customsku;
use App\Models\Dhl24data;
use File;
use ZipArchive;
use App\Models\Order;
use App\Models\Download;
use App\Models\OrderData;
use App\Models\Product;
use App\Models\Sku;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Intervention\Image\ImageManagerStatic as Image;
use PDF;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use \Statickidz\GoogleTranslate;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AjaxController extends Controller
{
    protected $files;
    protected $label;
    protected $orders;
    protected $folders;
    protected $idArray = [];
    protected $filename;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }


    // Move to
    public function moveTo(Request $request)
    {
        foreach ($request->ids as $id) {
            Order::find($id)->update(['pending' => $request->pending]);
        }

        return response()->json($request->ids);
    }


    // Remove order
    function removeOrder(Request $request)
    {
        foreach ($request->ids as $id) {
            Order::find($id)->update(['pending' => 3]);
        }

        return response()->json($request->ids);
    }


    // Download orders
    public function downloadOrders(Request $request)
    {
        if ($request->isMethod('post')) {
            // Generate zip file name
            $zipFileName = generateZipFileName($request->orders);

            $zip = new ZipArchive;
            if ($zip->open(public_path('archive/' . $zipFileName), ZipArchive::CREATE) === TRUE) {
                // Add Multiple file
                foreach ($request->orders as $order) {
                    $this->folders[] = $order['file'];

                    $this->files = scandir(public_path('archive/' . $order['file']));

                    foreach ($this->files as $key => $file) {
                        if ($file != "." && $file != "..") {
                            $zip->addFile(public_path('archive/' . $order['file'] . '/' . $file), $order['file'] . '/' . $order['file'] . '_' . $key . '.png');
                        }
                    }
                }

                $zip->close();
            }
            
            
            
            

            // Move orders to pending
            if (file_exists(public_path('archive/' . $zipFileName))) {
                foreach ($request->orders as $order) {
                    if ($request->type != 2) {
                        Order::where('id', '=', $order['id'])->update(['pending' => 1]);
                    }

                    Download::create([
                        "order_id" => $order['id'],
                        "created_at" => now()
                    ]);
                }
            }

            // Return response
            return response()->json([
                "status" => "success",
                "file" => $zipFileName
            ]);
        }
    }


    // Call DHL
    public function callDHL(Request $request)
    {
        // Get ids
        foreach ($request->orders as $idArray)
            array_push($this->idArray, $idArray['id']);

        // Get orders
        $this->orders = Order::whereIn('id', $this->idArray)
            ->with('files')
            ->with('dhl24data')
            ->get();

        // Call DHL
        foreach ($this->orders as $order) {
            $this->label = callDhl($order);
            // Save CALL TO DHL click to DB @Devi
            Order::find($order->id)->increment('call_Dhl');

            if ($this->label["status"] == 'success') {
                Order::where("id", "=", $order["id"])->update([
                    "pending"        => 2,
                    "label"          => $order->company_name==2?$this->label["label"]:null,
                    'shipment_order' => $this->label['shipmentNumber'],
                    'calledDHL_at'   => Carbon::now()
                ]);

                // Send response to OMV
                sendFulifilled($order->order_id, $this->label['shipmentNumber']);
            } else {
                // Save original call_Dhl_error to DB and "DHL Error" as DHL error @Devi
                Order::where('id', $order->id)->update(['full_dhl_error' => $this->label["status"], 'call_Dhl_error' => "Call DHL error"]);

                
                // Check DHL reject reason either in <statusMessage>
                $pos1 = strrpos($this->label["status"], '<statusMessage>');
                $pos2 = strrpos($this->label["status"], '</statusMessage>');
                // or in <statusmessage>
                if(strrpos($this->label["status"], '<statusMessage>') === false){
                    $pos1 = strrpos($this->label["status"], '<statusmessage>');
                    $pos2 = strrpos($this->label["status"], '</statusmessage>');
                }
                $text = substr($this->label["status"], $pos1 + 15, $pos2 - $pos1 - 15);
                $text = errMsgDe2En($text);
                Order::where('id', $order->id)->update(['call_Dhl_error' => $text]);
            }
        }

        return response()->json(['status' => $this->label["status"]]);
    }


    // clear dhl error
    public function clearDhlError(Request $request){
        Order::where('id', $request->id)->update(['call_Dhl_error' => null]);
        return response()->json(["status" => "success"]);
    }


    // Get order data
    public function getOrderData(Request $request)
    {
        $orderData = Order::find($request->id);
        $itemWeights = OrderData::where('order_id', $request->id)->get();
        $dhl24data = Dhl24data::where('order_id', $request->id)->first();

        return response()->json([
            "status"        => "success",
            "address1"      => $orderData->address1,
            "address2"      => $orderData->address2,
            "city"          => $orderData->city,
            "zip"           => $orderData->zip,
            "country"       => $orderData->country,
            "country_code"  => $orderData->country_code,
            "weights"       => $itemWeights,
            "company_name"    => $orderData->company_name,
            "dhl24data"     => $dhl24data
        ]);
    }


    // Save order data
    public function saveOrderData(Request $request)
    {
        $address1   = $request->data[0]["value"];
        $address2   = $request->data[1]["value"];
        $city       = $request->data[2]["value"];
        $zip        = $request->data[3]["value"];
        $country    = $request->data[4]["value"];
        $countryCode = $request->data[5]["value"];
        $orderId    = $request->data[6]["value"];

        $update = Order::where("id", "=", $orderId)->update([
            "address1"     => $address1,
            "address2"     => $address2,
            "city"         => $city,
            "zip"          => $zip,
            "country"      => $country,
            "country_code" => $countryCode
        ]);

        $order = Order::find($orderId);

        $dh24data = [];
        // $dh24data['order_id'] = $orderId;

        foreach ($request->data as $requestItem) {
            if (substr($requestItem["name"], 0, 7) === "weight-") {
                $id = substr($requestItem["name"], 7, strlen($requestItem["name"]) - 7);
                OrderData::where('id', $id)->update(['grams' => $requestItem["value"]]);
            }

            if ($requestItem["name"] == "packagetype")
                $dh24data['packagetype'] = $requestItem["value"];
            if ($requestItem["name"] == "width")
                $dh24data['width'] = $requestItem["value"];
            if ($requestItem["name"] == "height")
                $dh24data['height'] = $requestItem["value"];
            if ($requestItem["name"] == "length")
                $dh24data['length'] = $requestItem["value"];
            if ($requestItem["name"] == "weight")
                $dh24data['weight'] = $requestItem["value"];
            if ($requestItem["name"] == "content")
                $dh24data['content'] = $requestItem["value"];
        }

        if($order->company_name > 2){
            Dhl24data::updateOrCreate(['order_id' => $orderId], $dh24data);
        }

        if ($update) {
            return response()->json(["status" => "success"]);
        }
    }



    // Report **new
    public function getReportData(Request $request)
    {
        $year = $request->year;
        $month = $request->month + 1;

        $monthStart = $year . '-' . ($month < 10 ? '0' . $month : $month) . '-01';
        if ($month == 12) {
            $monthEnd = ($year + 1) . '-01-01';
        } else {
            $monthEnd = $year . '-' . (($month + 1 < 10) ? '0' . ($month + 1) : ($month + 1)) . '-01';
        }

        $orders = Order::where('pending', '!=', 3)
                        ->whereDate('created_at', '>=', $monthStart)
                        ->whereDate('created_at', '<', $monthEnd)
                        ->get();

        $days = array_fill(0, 31, 0);
        $weeks = array_fill(0, 4, 0);
        $countries = array();
        foreach ($orders as $order) {
            $day = date('d', strtotime($order->created_at));

            // Count by weeks (1-4)
            if ($day >= 1 && $day <= 7) {
                $weeks[0]++;
            } elseif ($day >= 8 && $day <= 15) {
                $weeks[1]++;
            } elseif ($day >= 16 && $day <= 23) {
                $weeks[2]++;
            } else {
                $weeks[3]++;
            }

            // Count by countries            
            if (array_key_exists($order->country_code, $countries)) {
                $countries[$order->country_code]['orders']++;
                $countries[$order->country_code]['price'] += $order->total_price_usd;
            } else {
                $countries[$order->country_code]['orders'] = 1;
                $countries[$order->country_code]['price'] = $order->total_price_usd;
                $countries[$order->country_code]['country'] = $order->country;
            }

            // Count by days
            $days[$day - 1]++;
        }

        return response()->json([
            "status" => "success",
            "byDays" => $days,
            "byWeeks" => $weeks,
            "byCountries" => $countries
        ]);
    }


    // Validate address
    public function validateAddress(Request $request)
    {

        $address1 = $request->data[0]["value"];
        $address2 = $request->data[1]["value"];
        $city = $request->data[2]["value"];
        $zip = $request->data[3]["value"];
        $country = $request->data[4]["value"];
        $country_code = $request->data[5]["value"];
        $id = $request->data[6]["value"];

        $key  = config('app.packages.' . $country_code . '.key');
        $pack = config('app.packages.' . $country_code . '.pack');
        $ccfrapaUser = env('CCFRAPA_USER');
        $ccfrapaPass = env('CCFRAPA_PASS');
        $exportDocument = "";

        if ($country_code == "CH") {
            $exportDocument = '<ExportDocument>
                <invoiceNumber>' . "#omv_" . $id . '</invoiceNumber>
                <exportType>COMMERCIAL_GOODS</exportType>
                <exportTypeDescription>TEST_1</exportTypeDescription>
                <termsOfTrade></termsOfTrade>
                <placeOfCommital>TEST_2</placeOfCommital>
                <additionalFee>0.00</additionalFee>
                <permitNumber></permitNumber>
                <attestationNumber></attestationNumber>
                <customsCurrency>EUR</customsCurrency>
                <WithElectronicExportNtfctn active="0"/>
                <ExportDocPosition>
                    <description>ExportPositionOne</description>
                    <countryCodeOrigin>DE</countryCodeOrigin>
                    <customsTariffNumber>123456</customsTariffNumber>
                    <amount>1</amount>
                    <netWeightInKG>0.01</netWeightInKG>
                    <customsValue>1.00</customsValue>
                </ExportDocPosition>
            </ExportDocument>';
        }
        $postfields = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cis="http://dhl.de/webservice/cisbase" xmlns:ns="http://dhl.de/webservices/businesscustomershipping/3.0">
            <soapenv:Header>
                <cis:Authentification>
                    <cis:user>'.$ccfrapaUser.'</cis:user>
                    <cis:signature>'.$ccfrapaPass.'</cis:signature>
                </cis:Authentification>
            </soapenv:Header>
            <soapenv:Body>
                <ns:ValidateShipmentOrderRequest>
                    <ns:Version>
                        <majorRelease>3</majorRelease>
                        <minorRelease>1</minorRelease>
                    </ns:Version>
                    <ShipmentOrder>
                        <sequenceNumber></sequenceNumber>
                        <Shipment>
                            <ShipmentDetails>
                                <product>' . $pack . '</product>
                                <cis:accountNumber>' . $key . '</cis:accountNumber>
                                <customerReference>#omv_123456</customerReference>
                                <shipmentDate>' . date("Y-m-d") . '</shipmentDate>
                                <costCentre></costCentre>
                                <ShipmentItem>
                                    <weightInKG>5</weightInKG>
                                    <lengthInCM>60</lengthInCM>
                                    <widthInCM>30</widthInCM>
                                    <heightInCM>15</heightInCM>
                                </ShipmentItem>
                                <Service>
                                    <Premium active="0"/>
                                    <Endorsement active="1" type="ABANDONMENT"/>
                                    <ParcelOutletRouting details="support@ohmyvalentine.com"/>
                                </Service>
                                <Notification>
                                    <recipientEmailAddress>support@ohmyvalentine.com</recipientEmailAddress>
                                </Notification>                
                            </ShipmentDetails>
                            <Shipper>
                                <Name>
                                    <cis:name1>Oh My Valentine</cis:name1>
                                </Name>
                                <Address>
                                    <cis:streetName>Friedrich-Wilhelm-Straße 55</cis:streetName>
                                    <cis:zip>12103</cis:zip>
                                    <cis:city>Berlin</cis:city>
                                    <cis:Origin>
                                        <cis:country>Germany</cis:country>
                                        <cis:countryISOCode>DE</cis:countryISOCode>
                                    </cis:Origin>
                                </Address>
                            </Shipper>
                            <Receiver>
                                <cis:name1>Empfänger Zeile 1</cis:name1>
                                <Address>
                                    <cis:streetName>'.$address1.' '.$address2.'</cis:streetName>
                                    <cis:zip>' . $zip . '</cis:zip>
                                    <cis:city>' . $city . '</cis:city>
                                    <cis:Origin>
                                        <cis:country>' . $country . '</cis:country>
                                        <cis:countryISOCode>' . $country_code . '</cis:countryISOCode>
                                        <cis:state/>
                                    </cis:Origin>
                                </Address>
                            </Receiver>
                            ' . $exportDocument . '
                        </Shipment>
                    </ShipmentOrder>        
                </ns:ValidateShipmentOrderRequest>
            </soapenv:Body>
        </soapenv:Envelope>';

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://cig.dhl.de/services/production/soap',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => $postfields,
            CURLOPT_HTTPHEADER => array(
                'soapAction: urn:validateShipment',
                'Authorization: Basic REdBXzE6MlVRWHM5TXhsV3pGMHRkb3ZrV2RVWUs4R2pFNXlJ',
                'Content-Type: application/xml; charset=utf-8'
                // ,'Cookie: _abck=37FECA9E15E4AC6E5DF8A670EBF3C64B~-1~YAAQ1HEGF3+d3TV5AQAAJLJRewWhV9SHpXS3RDAved/lqEjYgFgbvqEFpLkLUVRsXjvuRkPlN0425C6lqqpFC01ryjtTRKpwDtPUcMPWhRNDEthcyi0vi5wV4Yutcwf2ELcPD6wx9a7SktvgYmbxR58hoNiiq9UIWFAlPw9HKDfIXoFNY2EaHPRnRtIk3BVFn4cZWpdv+j8b4NV0/AbT1qVsDph5okkkx58Fw4hHSsGqzf9ZEynX1jiS1PNqb6XbeMCdgArBAWLke3C7PVHPMK607lEWG131ieF6LWBDOTxRJu7CEltBu6irwexyzfkP8w/h5mmZin3sEkwRPArsee0quZnd6GRoYoK/Uc+nITaFsrpQl9jLKg==~-1~-1~-1'
            ),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        // var_dump($response);

        $stC1 = strrpos($response, '<statusCode>');
        $stC2 = strrpos($response, '</statusCode>');
        $statusCode = substr($response, $stC1 + 12, $stC2 - $stC1 - 12);

        $stTx1 = strrpos($response, '<statusText>');
        $stTx2 = strrpos($response, '</statusText>');
        $statusText = substr($response, $stTx1 + 12, $stTx2 - $stTx1 - 12);

        $stMsg1 = strrpos($response, '<statusMessage>');
        $stMsg2 = strrpos($response, '</statusMessage>');
        $statusMsg = substr($response, $stMsg1 + 15, $stMsg2 - $stMsg1 - 15);

        return response()->json([
            'status' => 'success',
            'statusCode' => $statusCode,
            'statusText' => $statusText,
            'statusMsg' => errMsgDe2En($statusMsg),
        ]);
    }


    //Get Hold Image Data
    public function getProductImageData(Request $request)
    {
        $productId = $request->id;
        $productData = Product::where('id', $productId)->first();

        if ($productData === null)
            return response()->json(["status" => "error"]);

        $data = array(
            'productName' => $productData->product_name,
            'imageName'   => $productData->image
        );

        $skus = Sku::where('product_id', $productId)->get();

        $data['imgBase64'] = null;
        try{
            $data['imgBase64'] = base64_encode(file_get_contents(public_path('uploads/sku/' . $productData->image)));
        }catch(\Exception $x){}

        return response()->json([
            "status"    => "success",
            "productData" => $data,
            "skus"      => $skus
        ]);
    }


    // Single image upload
    public function uploadImage(Request $request)
    {
        $data = array();
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'file' => 'required|mimes:png,jpg,jpeg|max:20096'
        ]);

        if ($validator->fails()) 
            return response()->json(["status" => "error"]);

        if (!$request->file('file'))
            return response()->json(["status" => "error"]);

        $orderData = OrderData::where('id', $request->id)->first();

        $order = Order::where('id', $orderData->order_id)->first();
        $orderName = $order->name;
        // $orderName = '#1003';

        $imageIndex = $orderData->hold;
        //File::delete(public_path('archive/'.$request->orderName.'/'.$oldFileName));

        $file = $request->file('file');
        //$filename = $file->getClientOriginalName();        
        $extension = strtolower($file->getClientOriginalExtension());
        $location = public_path('archive/'.$orderName);
        $fullFilename = str_replace('#omv', '', $orderName) . '_' . $imageIndex . '.' . $extension;
        // $fullFilename = substr($oldFileName, 0, strpos($oldFileName, '.')).'.'.$extension;
        // Upload file
        $file->move($location, $fullFilename);

        //File::delete(public_path('uploads/thumbs/'.$oldFileName));
        // \File::copy(
        //     public_path('archive/'.$request->orderName.'/'.$fullFilename), 
        //     public_path('uploads/thumbs/'.$fullFilename)
        // );

        // open an image file
        $img = Image::make(public_path('archive/'.$orderName.'/'.$fullFilename));
        // resize the instance
        $img->resize(150, null, function ($constraint) {
            $constraint->aspectRatio();
        });
        // finally we save the image as a new file
        $img->save(public_path('uploads/thumbs/'.$fullFilename));

        OrderData::where('id', $request->id)->update([
            'image' => $fullFilename,
            'sku'   => str_replace('-HOLD', '', $orderData->sku),
            //'hold'  => null
        ]);
        $data['imgBase64'] =  base64_encode(file_get_contents(public_path('archive/' . $orderName . '/' . $fullFilename)));

        $rowToRemove = null;
        if(OrderData::where('order_id', $orderData->order_id)->whereNotNull('image')->count() == OrderData::where('order_id', $orderData->order_id)->count()){
            Order::where('id', $orderData->order_id)->update(['pending' => 0]);
            $rowToRemove = $orderData->order_id;
        }

        return response()->json([
            "status" => "success",
            "row_id" => $orderData->id,
            'assets' => asset(''),
            "thumbpath" => asset(('uploads/thumbs/'.$fullFilename)),
            "sku" => str_replace('-HOLD', '', $orderData->sku),
            "rowToRemove" => $rowToRemove,
            "result" => $data
        ]);
    }

    // Get SKU Data
    public function getSkuData(Request $request)
    {
        $skuData = SKU::where('id', $request->id)->first();
        if($skuData) 
            return response()->json(["status" => "success", "skuData" => $skuData]);        
        return response()->json(["status" => "error"]);    
    }

    // Save SKU
    public function saveSku(Request $request)
    {
        $skuData = SKU::where('name', $request->sku_name)->first();
        if($skuData){
            try{
                if($request->productid == $skuData->product_id && $skuData->id == $request->skuid){
                    $data = [
                        'size' => $request->sku_size,
                        'printfile_size' => $request->printfile_size,
                        'weight' => $request->sku_weight,
                        'price' => $request->sku_price,
                        'print_price' => $request->sku_print_price,
                        'stock_quantity' => $request->sku_quantity,
                    ];
                    return SKU::where('id', $skuData->id)->update($data) ? response()->json(["status" => "success", "action" => "update"]) : response()->json(["status" => "error"]); 
                }else{
                    return response()->json(["status" => "error1"]);
                }
            }catch(Exception $e){
                return response()->json(["status" => "error2"]);
            }
        }else{
            try{
                $data = [
                    'product_id' => $request->productid,
                    'name' => $request->sku_name,
                    'size' => $request->sku_size,
                    'printfile_size' => $request->printfile_size,
                    'weight' => $request->sku_weight,
                    'price' => $request->sku_price,
                    'print_price' => $request->sku_print_price,
                    'stock_quantity' => $request->sku_quantity,
                ];
                \Illuminate\Support\Facades\Log::info(
                    'product_id: ' . $request->productid.';  '.
                    'name: ' . $request->sku_name.';  '.
                    'size: ' . $request->sku_size.';  '.
                    'printfile_size: ' . $request->printfile_size.';  '.
                    'weight: ' . $request->sku_weight.';  '.
                    'price: ' . $request->sku_price.';  '.
                    'print_price: ' . $request->sku_print_price.';  '.
                    'stock_quantity: ' . $request->sku_quantity
                );
                $sku = SKU::create($data);
                return $sku ? response()->json(["status" => "success", "skuid" => $sku->id]) : response()->json(["status" => "error"]); 
            }catch(Exception $e){
                return response()->json(["status" => "error3"]);
            }
        }
    }

    // Delete SKU
    public function deleteSku(Request $request)
    {
        return Sku::where('id', $request->id)->delete() ? response()->json(["status" => "success"]) : response()->json(["status" => "error"]);    
    }


    // Save Product name
    public function saveProductName(Request $request)
    {
        try{
            if($request->product_id)
                return Product::where('id', $request->product_id)->update(['product_name' => $request->product_name]) ? response()->json(["status" => "success"]) : response()->json(["status" => "error"]); 
            
            $product = Product::create(['product_name' => $request->product_name]);
            return $product ? response()->json(["status" => "success", "product_id" => $product->id]) : response()->json(["status" => "error"]); 
        }catch(Exception $e){
            return response()->json(["status" => "error"]);
        }
    }


    // Product image upload
    public function uploadProductImage(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'file' => 'required|mimes:png,jpg,jpeg|max:8096'
        ]);

        if ($validator->fails())
            return response()->json(["status" => "error"]);

        if (!$request->file('file'))
            return response()->json(["status" => "error"]);

        if($request->product_id){
            // Replace product
            $productData = Product::where('id', $request->product_id)->first();
            $oldFileName = $productData->image;
            File::delete(public_path('uploads/sku/'.$oldFileName));
        

            $file = $request->file('file');
            //$filename = $file->getClientOriginalName();        
            $extension = strtolower($file->getClientOriginalExtension());
            $location = public_path('uploads/sku/');
            $fullFilename = substr($oldFileName, 0, strpos($oldFileName, '.')).'.'.$extension;
            // Upload file
            $file->move($location, $fullFilename);

            Product::where('id', $request->product_id)->update(['image' => $fullFilename]);
        }else{
            // Add new prduct
            $product = Product::create([]);
            $file = $request->file('file');
            //$filename = $file->getClientOriginalName();        
            $extension = strtolower($file->getClientOriginalExtension());
            $location = public_path('uploads/sku/');
            $fullFilename = $product->id.'.'.$extension;
            // Upload file
            $file->move($location, $fullFilename);
            return Product::where('id', $product->id)->update(['image' => $fullFilename]) ? 
                    response()->json(["status" => "success", "product_id" => $product->id]) : 
                    response()->json(["status" => "error"]);
        }

        return response()->json([
            "status" => "success"
        ]);
    }



    public function generateInvoice(Request $request){
        $from = $request->from;
        $to = $request->to;

        $sql = "SELECT order_data.sku, 
                       order_data.title AS product_name, 
                       COUNT(order_data.sku) AS quantity, 
                       SUM(order_data.price) AS price, 
                       SUM(order_data.print_price) AS print_price
                FROM orders
                LEFT JOIN order_data ON orders.id=order_data.order_id 
                WHERE calledDHL_at >= '$from' AND calledDHL_at <= '$to' AND (issue NOT IN ('broken', 'wrong', 'Missingparts') OR issue IS NULL)
                GROUP BY order_data.sku, order_data.title";        
        
        $result = DB::select($sql);

        $count = 0;
        $netSum = 0;
        foreach($result as $res){
            $count += $res->price != null && $res->print_price != null;
            $netSum += ($res->price + $res->print_price) * $res->quantity;
        }

        $data=[
            "result" => $result,
            "missingSKU" => $count < count($result),
            "netSum"    => $netSum,
            "from" => $from,
            "to" => $to
        ];

        $pdf = PDF::loadView('extra.invoice', $data);
        $path = public_path('invoices/');
        $fileName = date('YmdHis').'.'. 'pdf';
        $fullPath = $path.$fileName;
        $pdf->save($fullPath);
        return response()->download($fullPath);
        // return view('extra.invoice', $data)->render();
    }


    // Toggle user status
    public function toggleUserStatus(Request $request){
        if(!Auth::user()->administrator) 
            return response()->json(['status' => 'error', 'message' => 'You have no enough permission to complete this operation.']);
        if(!$request->id) 
            return response()->json(["status" => "error"]);
        
        $details = array();        
        $to = User::where('id', $request->id)->first()->email;

        if($request->status == 'true'){
            if($to && User::where('id', $request->id)->update(['active' => 1, 'company_name' => $request->id])){
                $details = [
                    "title" => "Activation account on Podsolution",    
                    "body" => "Your account is activated. Now you can log in to podsolutions \n Thank you for using our service."    
                ];
                // Mail::to($to)->send(new \App\Mail\ActivatedUserMail($details));
                // if(!Mail::failures())
                //     return response()->json(["status" => 'success']);
                $subject = $details["title"];
                $message = "<html><head><title>".$details["title"]."</title></head><body><p>".$details["body"]."</p></html>";
                // Always set content-type when sending HTML email
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                // More headers
                $headers .= 'From: <info@podsolutionshopify.com>' . "\r\n";
                mail($to, $subject, $message, $headers);
            }
        }else{
            if($to && User::where('id', $request->id)->update(['active' => 0])){
                $details = [
                    "title" => "Deactivation account on Podsolution",    
                    "body" => "Your account is deactivated."    
                ];
                // Mail::to($to)->send(new \App\Mail\ActivatedUserMail($details));
                // if(!Mail::failures())
                //     return response()->json(["status" => 'success']); 
                $subject = $details["title"];
                $message = "<html><head><title>".$details["title"]."</title></head><body><p>".$details["body"]."</p></html>";
                // Always set content-type when sending HTML email
                $headers = "MIME-Version: 1.0" . "\r\n";
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
                // More headers
                $headers .= 'From: <info@podsolutionshopify.com>' . "\r\n";
                mail($to, $subject, $message, $headers);                
            }
        }
        return response()->json(["status" => 'success']);
    }


    public function trackPackage(Request $request){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, env('DHL_TRACKING_URL').$request->tracking_number);
        $headers = ['DHL-API-Key:8YjbVZX6eJX093102jCXWH7xSHYKqxjK'];
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $output = curl_exec($curl);
        curl_close($curl);
        
        $res=json_decode($output);

        if (isset($res->shipments[0]))
            return response()->json(['status' => 'success', "data" => $res->shipments[0]]);
        return response()->json(['status' => 'error']);
    }


    // Get order details
    public function getOrderDetails(Request $request)
    {
        $orderData = Order::where('name', $request->ordername)->first();
        if($orderData)
            return response()->json(['status' => 'success', 'data' => $orderData]);
        return response()->json(['status' => 'error']);
    }


    public function getReplaceImage(Request $request)
    {
        $id = $request->orderDataId;        
        $orderData = OrderData::where('id', $id)->first();
        $order = Order::where('id', $orderData->order_id)->first();
        $orderName = $order->name;
        $data['imgBase64'] = $orderData->image ? base64_encode(file_get_contents(public_path('archive/' . $orderName . '/' . $orderData->{$request->key}))) : null;
        return response()->json([
            "status" => "success",
            "result" => $data
        ]);
    }


    public function uploadReplaceImage(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'file' => 'required|mimes:png,jpg,jpeg|max:20096'
        ]);

        if ($validator->fails())
            return response()->json(["status" => "error"]);

        if (!$request->file('file'))
            return response()->json(["status" => "error"]);
        
        if (!$request->orderDataId)
            return response()->json(["status" => "error"]);
        
        if ($request->imagen === null)
            return response()->json(["status" => "error"]);

        $orderData = OrderData::where('id', $request->orderDataId)->first();
        $order = Order::where('id', $orderData->order_id)->first();

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $location = public_path('archive/'.$order->name.'/');
        $fullFilename = str_replace('#omv', '', $order->name).'_'.$request->imagen.(str_replace('image', '', $request->key)).'.'.$extension;
        // Upload file
        $file->move($location, $fullFilename);
        OrderData::where('id', $request->orderDataId)->update([$request->key => $fullFilename]);

        Image::make($location.$fullFilename)                            
            ->resize(null, 60, function ($constraint) {
                $constraint->aspectRatio();
            })
            ->save(public_path('uploads/thumbs/' . $fullFilename));

        return response()->json([
            "status" => "success"
        ]);
    }



    public function getSkuPrices(Request $request){
        if(!Auth::user()->administrator) 
            return response()->json(['status' => 'error1', 'message' => 'You have no enough permission to complete this operation.']);
        if (!$request->userid)
            return response()->json(["status" => "error2"]);

        $userId = $request->userid;
        
        $skus = Sku::with(['customSku' => function($q) use($userId){
            $q->where('custom_sku.user_id', '=', $userId);
        }])->get();
        
        return response()->json([
            "status" => "success",
            "skus"   => $skus
        ]);       
    }


    public function updateCustomSku(Request $request){
        if(!Auth::user()->administrator) 
            return response()->json(['status' => 'error', 'message' => 'You have no enough permission to complete this operation.']);
        if (!$request->skuId || !$request->price || !$request->print_price || !$request->userId) return response()->json(["status" => "error"]);
        if(!filter_var($request->skuId, FILTER_VALIDATE_INT)) return response()->json(["status" => "error"]);
        if(!filter_var($request->userId, FILTER_VALIDATE_INT)) return response()->json(["status" => "error"]);
        if(!is_numeric($request->price)) return response()->json(["status" => "error"]);
        if(!is_numeric($request->print_price)) return response()->json(["status" => "error"]);

        $skuid = $request->skuId;
        $userid = $request->userId;
        $price = $request->price;
        $printPrice = $request->print_price;

        $customSku = Customsku::updateOrCreate([
            'user_id' => $userid,
            'sku_id'  => $skuid
        ], [
            'user_id' => $userid,
            'sku_id'  => $skuid,
            'price'   => $price,
            'print_price' => $printPrice,
            'enabled' => 1
        ]);
        // if(!$customSku->wasRecentlyCreated)
        //     $customSku->update(['enabled' => $customSku->enabled ? 0 : 1]);
        if($customSku)
            return response()->json(['status' => 'success']);


        
        return response()->json(["status" => "error"]);   
    } 
    
    
    public function deleteCustomSku(Request $request){ // removed
        if(!Auth::user()->administrator) 
            return response()->json(['status' => 'error', 'message' => 'You have no enough permission to complete this operation.']);
        if (!$request->customSkuId) return response()->json(["status" => "error"]);
        \App\Models\Customsku::where('id', $request->customSkuId)->delete();
        return response()->json([
            "status" => "success"
        ]);
    }

    public function toggleCustomSku(Request $request){
        if(!Auth::user()->administrator) 
            return response()->json(['status' => 'error', 'message' => 'You have no enough permission to complete this operation.']);
        if(!filter_var($request->skuId, FILTER_VALIDATE_INT)) return response()->json(["status" => "error"]);
        if(!filter_var($request->userId, FILTER_VALIDATE_INT)) return response()->json(["status" => "error"]);
        $skuid = $request->skuId;
        $userid = $request->userId;
        $sku = Sku::where('id', $skuid)->first();
        if($sku){
            $customSku = Customsku::firstOrCreate([
                'user_id' => $userid,
                'sku_id'  => $skuid
            ], [
                'user_id' => $userid,
                'sku_id'  => $skuid,
                'price'   => $sku->price,
                'print_price' => $sku->print_price,
                'enabled' => 1
            ]);
            if(!$customSku->wasRecentlyCreated)
                $customSku->update(['enabled' => $customSku->enabled ? 0 : 1]);
            return response()->json(['status' => 'success']);
        }
        return response()->json(["status" => "error"]);
    }


    public function getShopName(Request $request){
        if(!Auth::user()->administrator) 
            return response()->json(['status' => 'error', 'message' => 'You have no enough permission to complete this operation.']);
        if(!$request->userId)
            return response()->json(['status' => 'error']);
        if(!filter_var($request->userId, FILTER_VALIDATE_INT))
            return response()->json(['status' => 'error']);
        $user = User::where('id', $request->userId)->first();
        if($user)
            return response()->json(['status' => 'success', 'shopname' => $user->shop_name]);  
        return response()->json(['status' => 'error']);   
    }

    public function saveShopName(Request $request){
        if(!Auth::user()->administrator) 
            return response()->json(['status' => 'error', 'message' => 'You have no enough permission to complete this operation.']);
        if(!$request->userId || !$request->shopname)
            return response()->json(['status' => 'error']);
        if(!filter_var($request->userId, FILTER_VALIDATE_INT))
            return response()->json(['status' => 'error']);
        if(User::where('id', $request->userId)->update(['shop_name' => $request->shopname, 'company_name' => $request->userId]))
            return response()->json(['status' => 'success', 'message' => 'User with id '.$request->userId.' was set shop name '.$request->shopname]);
        return response()->json(['status' => 'error']);   
    }

    public function deleteOrder(Request $request){
        if(!Auth::user()->administrator) 
            return response()->json(['status' => 'error', 'message' => 'You have no enough permission to complete this operation.']);
        if(!filter_var($request->orderid, FILTER_VALIDATE_INT))
            return response()->json(['status' => 'error']);
        if(Order::where('id', $request->orderid)->delete())
            return response()->json(['status' => 'success', 'admin' => Auth::user()->administrator]);
        return response()->json(['status' => 'error']);
    }


    public function fixOrder(Request $request){
        // \Illuminate\Support\Facades\Log::info('fixOrder');
        if(!$request->orderid || !filter_var($request->orderid, FILTER_VALIDATE_INT))
            return response()->json(['status' => 'error']);
        
        $url = env('OMV_MANUAL_ORDER_RECEIVER_URL');//'https://poland-new.podsolutionshopify.com/manualorderreceiver';
        $order = Order::where('id', $request->orderid)->first('order_id');
        if($order){
            if($order->company_name > 2){
                $url = env('MERCHANT_MANUAL_ORDER_RECEIVER_URL');//'https://poland.podsolutionshopify.com/manualorderreceiver';
            }
            $postFields = [$order->order_id];
                        
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postFields));
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_ENCODING, '');
            curl_setopt($curl, CURLOPT_MAXREDIRS, 10);
            curl_setopt($curl, CURLOPT_TIMEOUT, 100);
            curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            $response = curl_exec($curl);
            curl_close($curl);        

            if($response) return response()->json(['status' => 'success']);
        }
        return response()->json(['status' => 'error']);
    }
}
