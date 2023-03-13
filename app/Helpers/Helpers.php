<?php

use App\Models\Order;
use App\Models\OrderData;
use App\Models\Dhl24data;
use App\Custom\Dhl24\DHL24;
use App\Custom\Dhl24\Utils;
use App\Custom\Dhl24\Structures\Address;
use App\Custom\Dhl24\Structures\PaymentData;
use App\Custom\Dhl24\Structures\Piece;
use App\Custom\Dhl24\Structures\ReceiverAddress;
use App\Custom\Dhl24\Structures\ServiceDefinition;
use App\Custom\Dhl24\Structures\ShipmentFullData;
use App\Custom\Dhl24\Structures\ItemToPrint;


// Date from and to
if (!function_exists('dateFromTo')) {
    function fromTo($date, $day, $to)
    {

        $divided  = explode(' ', $date);
        $year     = $divided[3];
        $month    = date("m", strtotime($divided[1]));
        $from     = str_pad($day, 2, "0", STR_PAD_LEFT);

        $dateFrom = $year . '-' . $month . '-' . $from;
        $dateTo   = $year . '-' . $month . '-' . str_pad($to, 2, "0", STR_PAD_LEFT);

        return [
            "from" => $dateFrom,
            "to"   => $dateTo
        ];
    }
}


// Fix address
if (!function_exists('fixAddress')) {
    function fixAddress($fix)
    {
        $address = str_replace("ß", "ss", $fix);

        return $address;
    }
}


// Week days
if (!function_exists('weekdays')) {
    function weekdays()
    {
        return ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    }
}


// Change image name
if (!function_exists('changeImageName')) {
    function changeImageName($img, $order_number, $key, $side)
    {
        $url   = explode("?", $img)[0];
        $total = count(explode(".", $url));
        $type  = explode(".", $url)[$total - 1];
        $image = $order_number . "_" . $key . ($side>0?'_'.($side+1):''). "." . $type;

        return ["image" => $image, "url" => $url];
    }
}


// Generate zip file name
if (!function_exists('generateZipFileName')) {
    function generateZipFileName($files, $name = null)
    {
        foreach ($files as $file) {
            $name .= $file['file'];
        }

        return md5($name) . '.zip';
    }
}


// Generate PDF
if (!function_exists('generatePDF')) {
    function generatePDF($input)
    {
        $data = ["data" => $input['content']];

        view()->share('data', $data);

        $pdf = PDF::loadView('pdf.pdfview', $data);

        $pdf->save(public_path('archive/' . $input['directory'] . '/orderData.pdf'));
    }
}


// Download documents
if (!function_exists('downloadDocs')) {
    function downloadDocs($url)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        $data = curl_exec($ch);
        curl_close($ch);

        return $data;
    }
}


// Call DHL
if (!function_exists('callDhl')) {
    function callDhl($data)
    {
        if($data->company_id==2){
            $sum     = 0;
            $total   = 0;
            $message = null;
            $exportDocument = '';

            foreach ($data->files as $product) {
                $sum   += $product->quantity;
                $total += ($product->grams * $product->quantity);
            }

            if ($data->country_code == "CH") {
                $item = '';

                if (isset($data->files) && !is_null($data->files)) {

                    foreach ($data->files as $file) {
                        $item .= '<ExportDocPosition>
                            <description>' . str_replace("&", "&amp;", $file->title) . '</description>
                            <amount>' . $file->quantity . '</amount>
                            <netWeightInKG>' . ($file->grams / 1000) . '</netWeightInKG>
                            <customsValue>3.90</customsValue>
                        </ExportDocPosition>';
                    }
                }

                $exportDocument = '<ExportDocument>
                    <invoiceNumber>' . str_replace("#omv", "#omv_", $data->name) . '</invoiceNumber>
                    <exportType>COMMERCIAL_GOODS</exportType>
                    <exportTypeDescription>TEST_1</exportTypeDescription>
                    <placeOfCommital>TEST_2</placeOfCommital>
                    <additionalFee>' . (3.90 * $sum) . '</additionalFee>
                    <customsCurrency>EUR</customsCurrency>
                    <WithElectronicExportNtfctn active="0"/>
                    ' . $item . '
                </ExportDocument>';
            }

            $curl = curl_init();
            $key  = config('app.packages.' . $data->country_code . '.key');
            $pack = config('app.packages.' . $data->country_code . '.pack');
            $ccfrapaUser = env('CCFRAPA_USER');
            $ccfrapaPass = env('CCFRAPA_PASS');

            $postfields = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:cis="http://dhl.de/webservice/cisbase" xmlns:ns="http://dhl.de/webservices/businesscustomershipping/3.0">
                <soapenv:Header>
                    <cis:Authentification>
                        <cis:user>'.$ccfrapaUser.'</cis:user>
                        <cis:signature>'.$ccfrapaPass.'</cis:signature>
                    </cis:Authentification>
                </soapenv:Header>
                <soapenv:Body>
                    <ns:CreateShipmentOrderRequest>
                        <ns:Version>
                            <majorRelease>3</majorRelease>
                            <minorRelease>1</minorRelease>
                        </ns:Version>
                        <ShipmentOrder>
                            <sequenceNumber/>
                            <Shipment>
                                <ShipmentDetails>
                                <product>' . $pack . '</product>
                                <cis:accountNumber>' . $key . '</cis:accountNumber>
                                <customerReference>' . str_replace("#omv", "#omv_", $data->name) . '</customerReference>
                                <shipmentDate>' . date('Y-m-d') . '</shipmentDate>
                                <cis:EKP>6332949984</cis:EKP>
                                <costCentre>0.00</costCentre>
                                <ShipmentItem>
                                    <weightInKG>' . $total / 1000 . '</weightInKG>
                                </ShipmentItem>
                                <Service>
                                    <Premium active="0"/>
                                    <Endorsement active="1" type="ABANDONMENT"/>
                                    <ParcelOutletRouting details="support@ohmyvalentine.com"/>
                                </Service>
                                <Notification>
                                    <recipientEmailAddress>' . $data->email . '</recipientEmailAddress>
                                </Notification>
                                </ShipmentDetails>
                                <Shipper>
                                <Name>
                                    <cis:name1>Oh My Valentine</cis:name1>
                                </Name>
                                <Address>
                                    <cis:streetName>Friedrich-Wilhelm-Straße</cis:streetName>
                                    <cis:streetNumber>55</cis:streetNumber>
                                    <cis:zip>12103</cis:zip>
                                    <cis:city>Berlin</cis:city>
                                    <cis:Origin>
                                        <cis:countryISOCode>DE</cis:countryISOCode>
                                    </cis:Origin>
                                </Address>
                                <Communication>
                                    <cis:email>support@ohmyvalentine.com</cis:email>
                                    <cis:contactPerson>Omv Support Team</cis:contactPerson>
                                </Communication>
                                </Shipper>
                                <Receiver>
                                <cis:name1>' . $data->first_name . ' ' . $data->last_name . '</cis:name1>
                                <Address>
                                    <cis:streetName>' . $data->address1 . '</cis:streetName>
                                    <cis:streetNumber>' . $data->address2 . '</cis:streetNumber>
                                    <cis:zip>' . $data->zip . '</cis:zip>
                                    <cis:city>' . $data->city . '</cis:city>
                                    <cis:Origin>
                                        <cis:countryISOCode>' . $data->country_code . '</cis:countryISOCode>
                                    </cis:Origin>
                                </Address>
                                <Communication>
                                    <cis:email>' . $data->email . '</cis:email>
                                    <cis:contactPerson>' . $data->first_name . ' ' . $data->last_name . '</cis:contactPerson>
                                </Communication>
                                </Receiver>
                                ' . $exportDocument . '
                            </Shipment>
                            <PrintOnlyIfCodeable active="1"/>
                        </ShipmentOrder>
                        <labelResponseType>URL</labelResponseType>
                        <groupProfileName/>
                        <labelFormat/>
                        <labelFormatRetoure/>
                        <combinedPrinting>1</combinedPrinting>
                    </ns:CreateShipmentOrderRequest>
                </soapenv:Body>
                </soapenv:Envelope>';

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
                    'soapAction: urn:createShipmentOrder',
                    'Authorization: Basic REdBXzE6MlVRWHM5TXhsV3pGMHRkb3ZrV2RVWUs4R2pFNXlJ',
                    'Content-Type: application/xml; charset=utf-8',
                    'Cookie: _abck=37FECA9E15E4AC6E5DF8A670EBF3C64B~-1~YAAQ1HEGF3+d3TV5AQAAJLJRewWhV9SHpXS3RDAved/lqEjYgFgbvqEFpLkLUVRsXjvuRkPlN0425C6lqqpFC01ryjtTRKpwDtPUcMPWhRNDEthcyi0vi5wV4Yutcwf2ELcPD6wx9a7SktvgYmbxR58hoNiiq9UIWFAlPw9HKDfIXoFNY2EaHPRnRtIk3BVFn4cZWpdv+j8b4NV0/AbT1qVsDph5okkkx58Fw4hHSsGqzf9ZEynX1jiS1PNqb6XbeMCdgArBAWLke3C7PVHPMK607lEWG131ieF6LWBDOTxRJu7CEltBu6irwexyzfkP8w/h5mmZin3sEkwRPArsee0quZnd6GRoYoK/Uc+nITaFsrpQl9jLKg==~-1~-1~-1'
                ),
            ));

            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error    = curl_errno($curl);

            curl_close($curl);

            // If error response
            if (empty($response) || $response === FALSE || $error)
                return ["status" => "error", "message" => 'No response'];      

            // Get response
            preg_match('/<labelUrl>(.*?)<\/labelUrl>/s', $response, $match);

            if (isset($match[1]) && $match[1] != '') {
                // Get tracking
                preg_match('/<shipmentNumber>(.*?)<\/shipmentNumber>/s', $response, $shipment);
                $tracking = downloadDocs($match[1]);
                file_put_contents(public_path("archive/" . $data->name . "/label.pdf"), $tracking);

                // Get export label url
                preg_match('/<exportLabelUrl>(.*?)<\/exportLabelUrl>/s', $response, $exportLabelUrl);

                if (isset($exportLabelUrl[1]) && $exportLabelUrl[1] != '') {
                    $export = downloadDocs($exportLabelUrl[1]);
                    file_put_contents(public_path("archive/" . $data->name . "/exportLabel.pdf"), $export);
                }

                $message .= "<div class='text-success'>" . $data["id"] . " Success</div>";

                return [
                    "status"         => "success",
                    "label"          => $match[1],
                    "message"        => $message,
                    "shipmentNumber" => $shipment[1],
                    "exportLabelUrl" => isset($exportLabelUrl[1]) && $exportLabelUrl[1] != '' ? $exportLabelUrl[1] : null
                ];
            } else {
                preg_match_all('/<Status>(.*?)<\/Status>/s', $response, $match);

                if ($match[1]) {
                    foreach ($match[1] as $error) {
                        $message .= "<div class='text-danger'>" . $error . "</div>";
                    }
                } else {
                    return "Unknown error";
                }


                return [
                    "status"       => $message,
                    "errorMessage" => nl2br($message)
                ];
            }
        }else{
            // non OMV merchant           
            $dhl = new DHL24(env('DHL24_USER'), env('DHL24_PASS'), env('DHL24_CUSTOMER'), false); // user, pass, clientId, sandbox
            //$dhl = new DHL24('DZIDZISHVILI_test', 'hi%.FaeYPY*sowz', 6000000, true); // user, pass, clientId, sandbox

            $addressStructure = (new Address())
                ->setName('HEYDEAR')
                ->setPostalCode('71-211')
                ->setCity('Szczecin')
                ->setStreet('Szeroka')
                ->setHouseNumber('2') // required
                // ->setApartmentNumber('.') // optional
                ->setContactPerson('Adam Poszalski')
                ->setContactPhone('884830130')
                ->setContactEmail('cyfrowypress@gmail.com')
                ->structure();

            //Receiver address
            $receiverAddressStructure = (new ReceiverAddress())
                ->setAddressType(ReceiverAddress::ADDRESS_TYPE_B)
                ->setCountry($data->country_code)
                ->setName($data->first_name . ' ' . $data->last_name)
                ->setPostalCode($data->zip)
                ->setCity($data->city)
                ->setStreet($data->address1)
                ->setHouseNumber($data->address2) // required
                // ->setApartmentNumber('.') // optional
                ->setContactPerson($data->first_name . ' ' . $data->last_name)
                ->setContactPhone($data->phone)
                ->setContactEmail($data->email)
                ->structure();

            //Package settings
            $pieceStructure = (new Piece())
                ->setType($data->dhl24data->packagetype==1?Piece::TYPE_PACKAGE:($data->dhl24data->packagetype==2?Piece::TYPE_ENVELOPE:Piece::TYPE_PALLET))
                ->setWidth($data->dhl24data->width)
                ->setHeight($data->dhl24data->height)
                ->setLength($data->dhl24data->length)
                ->setWeight($data->dhl24data->weight)
                ->setQuantity(1)
                ->setNonStandard(false)
                ->structure();

            //Payment
            $paymentStructure = (new PaymentData())
                ->setPaymentMethod(PaymentData::PAYMENT_METHOD_BANK_TRANSFER)
                ->setPayerType(PaymentData::PAYER_TYPE_SHIPPER)
                ->setAccountNumber(env('DHL24_CUSTOMER')) // required, CLIENTID 6000000
                ->structure();

            //Service
            $serviceDefinitionStructure = (new ServiceDefinition())
                ->setProduct(ServiceDefinition::PRODUCT_CONNECT_SHIPMENT) //PRODUCT_DOMESTIC_SHIPMENT PRODUCT_CONNECT_SHIPMENT
                ->setInsurance(false)
                ->setInsuranceValue(0)
                ->structure();

            //Group all data to shipment structure
            $shipmentFullDataStructure = (new ShipmentFullData())
                ->setShipper($addressStructure)
                ->setReceiver($receiverAddressStructure)
                ->setPieceList([$pieceStructure])
                ->setPayment($paymentStructure)
                ->setService($serviceDefinitionStructure)
                ->setShipmentDate(\date(ShipmentFullData::DATE_FORMAT, time()-time()%10000))
                ->setContent('Some content')
                ->setSkipRestrictionCheck(true)
                ->structure();

            $result = $dhl->createShipments($shipmentFullDataStructure);
            $shipmentId = $result['shipmentId'];
            try {
                $result = $dhl->createShipments($shipmentFullDataStructure);                
                if($result && $result['shipmentId']){
                    $shipmentId = $result['shipmentId'];
                    try {
                        $itemsToPrint = [];                    
                        $itemsToPrint[] = (new ItemToPrint())
                            ->setLabelType(ItemToPrint::LABEL_TYPE_BLP) //LABEL_TYPE_LP LABEL_TYPE_BLP LABEL_TYPE_LBLP LABEL_TYPE_ZBLP
                            ->setShipmentId($shipmentId)
                            ->structure(); 
                        $result = $dhl->getLabels($itemsToPrint);                    
                        $savedLabelsName = Utils::saveLabels($result, 'archive/'.$data->name.'/');
                        
                        return [
                            "status"         => "success",
                            // "label"          => $match[1],
                            // "message"        => $message,
                            "shipmentNumber" => $shipmentId,
                            // "exportLabelUrl" => isset($exportLabelUrl[1]) && $exportLabelUrl[1] != '' ? $exportLabelUrl[1] : null
                        ];
                    } catch (\Throwable $th) {
                        \Illuminate\Support\Facades\Log::info('A: '.json_encode($th->getMessage()));
                        return ["status" => $th->getMessage()];
                        echo $th->getMessage();
                    }
                }
            } catch (\Throwable $th) {
                \Illuminate\Support\Facades\Log::info('B: '.json_encode($th->getMessage()));
                return ["status" => $th->getMessage()];
                echo $th->getMessage();
            }
            return ["status" => "fail"];
        }
    }
}


// Send fulfilled order
if (!function_exists('sendFulifilled')) {
    function sendFulifilled($orderID, $tracking)
    {
        $url = env('OMV_CHANGETRACK_URL');//'https://poland-new.podsolutionshopify.com/changetrack';
        $order = Order::where('order_id', $orderID)->first('id');
        if($order->company_id > 2) $url = env('MERCHANT_CHANGETRACK_URL');//'https://poland.podsolutionshopify.com/changetrack';
        $lineItems = OrderData::where('order_id', $order->id)->pluck('line_item_id')->toArray();
        $li = [];
        foreach($lineItems as $lineitem) array_push($li, ['id' => $lineitem]);
        $postFields = [
          'orderID' => $orderID, 
          'trackID' => $tracking, 
          'lineItemsID' => $li,
          'shop' => $order->shop
        ];
        
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($postFields),
            CURLOPT_HTTPHEADER => array('Content-Type: text/plain'),
        ));
        $response = curl_exec($curl);
        curl_close($curl);
    }
}


// get Ratio Color
if (!function_exists('getRatioColor')) {
    function getRatioColor($new, $prev)
    {
        return $new == $prev ? 'orange' : ($new > $prev ? 'green' : 'red');
    }
}


// Get Ratio Icon
if (!function_exists('getRatioIcon')) {
    function getRatioIcon($new, $prev)
    {
        return $new == $prev ? '' : ($new > $prev ? 'fas fa-caret-up' : 'fas fa-caret-down');
    }
}


// Translate error message
if (!function_exists('errMsgDe2En')) {
    function errMsgDe2En($errMsg){
        $errMsg = str_replace('.', '', $errMsg);
        if(strpos(strtolower($errMsg), 'bitte geben sie eine hausnummer an') !== false){
            return "Missing house number";
        }elseif(strpos(strtolower($errMsg), 'die postleitzahl konnte nicht gefunden werden') !== false){
            return "Missing postal code";
        }elseif(strpos(strtolower($errMsg), 'bitte geben sie ein gewicht an') !== false){
            return "Missing weight";
        }elseif(mb_strpos(strtolower($errMsg), 'die angegebene straße kann nicht gefunden werden') !== false || strpos($errMsg, 'Die angegebene Straße kann nicht gefunden werden') !== false){
            return "Unknown street";
        }elseif(strpos(strtolower($errMsg), 'die sendung ist nicht leitcodierbar') !== false){
            return "The location is not known for this postal code";
        }elseif(strpos(strtolower($errMsg), 'es handelt sich um eine ungültige postleitzahl') !== false || strpos(strtolower($errMsg), 'bitte geben sie eine gültige postnummer an') !== false){
            return "Invalid postal code";
        }elseif(strpos(strtolower($errMsg), 'die angegebene hausnummer kann nicht gefunden werden') !== false){
            return "Unknown house number";
        }elseif(strpos(strtolower($errMsg), 'der ort ist zu dieser plz nicht bekannt') !== false){
            return "Unknown place for this postcode";
        }elseif(strpos(strtolower($errMsg), 'der eingegebene wert ist zu lang') !== false){
            return "Entered value is too long";
        }elseif(strpos(strtolower($errMsg), 'das angegebene produkt ist für das land nicht verfügbar') !== false){
            return "Product not available for the country";
        }elseif(strpos(strtolower($errMsg), 'bitte beachten sie, dass der service vorausverfügung für sendungen mit dhl paket international verpflichtend ist') !== false){
            return "Advance disposal service is mandatory";
        }elseif(mb_strpos(strtolower($errMsg), 'der webservice wurde ohne fehler ausgeführt') !== false){
            return "Address validation passed";
        }else{
            return $errMsg;
        }
    }
}


// Month serial to year/month
if (!function_exists('mnthSer2YrMnth')) {
    function mnthSer2YrMnth($monthSerial){
        $lastMonth = $monthSerial % 12;
        $lastYear  = (int) ($monthSerial / 12);
        if($lastMonth == 0){
            $lastMonth = 12;
            $lastYear --;
        }
        return ['year' => $lastYear, 'month' => $lastMonth];
    }
}


// calculate fee base on weight
if (!function_exists('calculateWeightFee')) {
    function calculateWeightFee($grams, $countryCode){
		if($countryCode == 'AT'){
			if($grams > 0 && $grams < 1000){
				return 6.04;
			}
			if($grams >= 1000 && $grams < 5000){
				return 6.13;
			}
		}
		if($countryCode == 'DE'){
			if($grams > 0 && $grams < 1000){
				return 4.58;
			}
			if($grams >= 1000 && $grams < 5000){
				return 4.67;
			}
		}
        return 0;
    }
}

if (!function_exists('days_in_month')) {
    function days_in_month($month, $year){
        return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year % 400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
    }
}