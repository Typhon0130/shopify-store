<?php

namespace App\Custom\Dhl24\Structures;

use App\Custom\Dhl24\Exceptions\InvalidStructureException;

class ShipmentBasic
{
    /**
     * Shipment id
     *
     * required
     * 
     * @var int 
    */
    private $shipmentId = '';

    /**
     * Date of shipment creation, in the format YYYY-MM-DD
     *
     * required
     * 
     * @var string 
    */
    private $created = '';

    /**
     * Sender's data
     *
     * required
     *
     * Address structure
     *
     * @var array
     */
    private $shipper = [];

    /**
     * Receivers's data
     *
     * required
     *
     * Address structure
     *
     * @var array
     */
    private $receiver = [];

    /**
     * Information whether a courier is ordered for this shipment
     *
     * required
     * 
     * @var string 
    */
    private $orderStatus = '';

    public function setShipmentId(string $shipmentId): ShipmentBasic
    {
        $this->shipmentId = $shipmentId;

        return $this;
    }

    public function setCreated(string $created): ShipmentBasic
    {
        $this->created = $created;

        return $this;
    }

    public function setShipper(array $shipper): ShipmentBasic
    {
        $this->shipper = $shipper;

        return $this;
    }

    public function setReceiver(array $receiver): ShipmentBasic
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function setOrderStatus(string $orderStatus): ShipmentBasic
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    /**
     * The structure provides basic data about the shipment. 
     * 
     * Elements of this type are returned, for example, by the getMyShipments method.
     *
     * @throws InvalidStructureException
     * 
     * @return array 
    */
    public function structure(): array
    {
        $structure = [];

        if($this->shipmentId === 0){
            throw new InvalidStructureException("Shipment basic shipment id required");
        }

        $structure['shipmentId'] = $this->shipmentId;

        $structure['created'] = $this->created;

        if(\count($this->shipper) === 0){
            throw new InvalidStructureException("Shipment basic shipper required");
        }

        $structure['shipper'] = $this->shipper;
        
        if(\count($this->receiver) === 0){
            throw new InvalidStructureException("Shipment basic receiver required");
        }

        $structure['receiver'] = $this->receiver;

        $structure['orderStatus'] = $this->orderStatus;

        return $structure;
    }
}