<?php

namespace App\Custom\Dhl24;

use App\Custom\Dhl24\Client;
use App\Custom\Dhl24\Exceptions\DHL24Exception;
use App\Custom\Dhl24\Exceptions\SoapException;
use App\Custom\Dhl24\Structures\Address;
use App\Custom\Dhl24\Structures\AuthData;
use App\Custom\Dhl24\Structures\ItemToPrintResponse;
use App\Custom\Dhl24\Structures\ShipmentBasic;

class DHL24
{
    /**
     * Soap client
     *
     * @var Client
     */
    private $client = null;

    /**
     * Auth data structure
     *
     * @var array
     */
    private $authData = [];

    /**
     * DHL account number
     *
     * @var string
     */
    private $accountNumber = '';

    public function __construct(string $login, string $password, string $accountNumber, bool $sandbox = false)
    {
        $this->client = new Client($sandbox);

        if(!$this->client){
            throw new SoapException("Connection error");
        }

        $this->authData = (new AuthData($login, $password))->structure();

        $this->accountNumber = $accountNumber;
    }

    /**
     * WebApi version
     *
     * @throws SoapFault
     *
     * @return string
     */
    public function getVersion(): string
    {
        $result = $this->client->getVersion();

        return $result->getVersionResult;
    }

    /**
     * Create shipment
     *
     * @param array $shipments ShipmentFullData structure
     *
     * @throws SoapException
     * @throws SoapFault
     * @throws InvalidStructureException
     *
     * @return array
     */
    public function createShipments(array $shipments): array
    {
        $params = [
            'authData' => $this->authData,
            'shipments' => $shipments,
        ];

        \Illuminate\Support\Facades\Log::info('DHL24[shipment?]: '.json_encode($shipments));

        $result = $this->client->createShipments($params);
        \Illuminate\Support\Facades\Log::info('DHL24[response]: '.json_encode($result));

        if (!isset($result->createShipmentsResult)) {
            throw new SoapException('Invalid response structure');
        }

        $shipper = (new Address())
            ->setName($result->createShipmentsResult->item->shipper->name)
            ->setPostalCode($result->createShipmentsResult->item->shipper->postalCode)
            ->setCity($result->createShipmentsResult->item->shipper->city)
            ->setStreet($result->createShipmentsResult->item->shipper->street)
            ->setHouseNumber($result->createShipmentsResult->item->shipper->houseNumber)
            // ->setApartmentNumber($result->createShipmentsResult->item->shipper->apartmentNumber)
            ->setApartmentNumber($result->createShipmentsResult->item->shipper->apartmentNumber?$result->createShipmentsResult->item->shipper->apartmentNumber:'')
            ->setContactPerson($result->createShipmentsResult->item->shipper->contactPerson)
            ->setContactPhone($result->createShipmentsResult->item->shipper->contactPhone)
            ->setContactEmail($result->createShipmentsResult->item->shipper->contactEmail)
            ->structure();

        $receiver = (new Address())
            ->setName($result->createShipmentsResult->item->receiver->name)
            ->setPostalCode($result->createShipmentsResult->item->receiver->postalCode)
            ->setCity($result->createShipmentsResult->item->receiver->city)
            ->setStreet($result->createShipmentsResult->item->receiver->street)
            ->setHouseNumber($result->createShipmentsResult->item->receiver->houseNumber)
            // ->setApartmentNumber($result->createShipmentsResult->item->receiver->apartmentNumber)
            ->setApartmentNumber($result->createShipmentsResult->item->receiver->apartmentNumber?$result->createShipmentsResult->item->receiver->apartmentNumber:'')
            ->setContactPerson($result->createShipmentsResult->item->receiver->contactPerson)
            ->setContactPhone($result->createShipmentsResult->item->receiver->contactPhone)
            ->setContactEmail($result->createShipmentsResult->item->receiver->contactEmail)
            ->structure();

        return (new ShipmentBasic())
            ->setShipmentId($result->createShipmentsResult->item->shipmentId)
            ->setCreated($result->createShipmentsResult->item->created)
            ->setShipper($shipper)
            ->setReceiver($receiver)
            ->setOrderStatus($result->createShipmentsResult->item->orderStatus)
            ->structure();
    }

    /**
     * Get labels
     *
     * @param array $itemsToPrint Array of ItemToPrint structures, 3 items max
     *
     * @throws SoapException
     * @throws SoapFault
     * @throws InvalidStructureException
     * @throws DHL24Exception
     *
     * @return array
     */
    public function getLabels(array $itemsToPrint): array
    {
        $itemsToPrintCount = \count($itemsToPrint);

        if ($itemsToPrintCount === 0) {
            throw new DHL24Exception('ItemsToPrint require minimum 1 element');
        }

        if ($itemsToPrintCount > 3) {
            throw new DHL24Exception('3 ItemToPrint structures maximum');
        }

        $params = [
            'authData' => $this->authData,
            'itemsToPrint' => $itemsToPrint,
        ];

        $result = $this->client->getLabels($params);

        if (!isset($result->getLabelsResult)) {
            throw new SoapException('Invalid response structure');
        }

        return (new ItemToPrintResponse())->fromResponse($result->getLabelsResult);
    }
}
