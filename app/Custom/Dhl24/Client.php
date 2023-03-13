<?php

namespace App\Custom\Dhl24;

class Client extends \SoapClient
{
    const WSDL_LIVE = "https://dhl24.com.pl/webapi2";

    const WSDL_SANDBOX = "https://sandbox.dhl24.com.pl/webapi2";

    public function __construct(bool $sanbox = false)
    {
        $wsdl = ($sanbox) ? self::WSDL_SANDBOX : self::WSDL_LIVE;

        parent::__construct($wsdl);
    }

}
