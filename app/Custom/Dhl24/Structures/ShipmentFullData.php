<?php

namespace App\Custom\Dhl24\Structures;

use App\Custom\Dhl24\Exceptions\InvalidStructureException;

class ShipmentFullData
{
    public const DATE_FORMAT = 'Y-m-d';

    /**
     * Sender's data transferred in the address structure
     *
     * required
     *
     * Address structure
     *
     * @var array
     */
    private $shipper = [];

    /**
     * Recipient data provided in the address structure
     *
     * required
     *
     * ReceiverAddress structure
     *
     * @var array
     */
    private $receiver = [];

    /**
     * Package data, a list of PieceDefinition elements
     *
     * required
     *
     * array of PieceDefinition structures
     *
     * @var array
     */
    private $pieceList = [];

    /**
     * Payer and payment data
     *
     * required
     *
     * PaymentData structure
     *
     * @var array
     */
    private $payment = [];

    /**
     * Data on the selected shipping service and additional services
     *
     * required
     *
     * ServiceDefinition structure
     *
     * @var array
     */
    private $service = [];

    /**
     * Date of dispatch (visible on the consignment note) - in the format YYYY-MM-DD
     *
     * required
     *
     * @var string
     */
    private $shipmentDate = '';

    /**
     * Whether to skip the restriction check
     *
     * @var bool
     */
    private $skipRestrictionCheck = false;

    /**
     * Additional comment (visible on the waybill)
     *
     * max (100)
     *
     * @var string
     */
    private $comment = '';

    /**
     * Package content
     *
     * required
     *
     * @var string
     */
    private $content = '';

    /**
     * Shipment reference number
     *
     * @var string
     */
    private $reference = '';

    /**
     * Customs clearance data.
     *
     * CustomsData structure
     *
     * @var array
     */
    private $customs = [];

    public function setShipper(array $shipper): ShipmentFullData
    {
        $this->shipper = $shipper;

        return $this;
    }

    public function setReceiver(array $receiver): ShipmentFullData
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function setPieceList(array $pieceList): ShipmentFullData
    {
        $this->pieceList = $pieceList;

        return $this;
    }

    public function setPayment(array $payment): ShipmentFullData
    {
        $this->payment = $payment;

        return $this;
    }

    public function setService(array $service): ShipmentFullData
    {
        $this->service = $service;

        return $this;
    }

    public function setShipmentDate(string $shipmentDate): ShipmentFullData
    {
        $this->shipmentDate = $shipmentDate;

        return $this;
    }

    public function setSkipRestrictionCheck(bool $skipRestrictionCheck): ShipmentFullData
    {
        $this->skipRestrictionCheck = $skipRestrictionCheck;

        return $this;
    }

    public function setComment(string $comment): ShipmentFullData
    {
        $this->comment = $comment;

        return $this;
    }

    public function setContent(string $content): ShipmentFullData
    {
        $this->content = $content;

        return $this;
    }

    public function setReference(string $reference): ShipmentFullData
    {
        $this->reference = $reference;

        return $this;
    }

    public function setCustoms(array $customs): ShipmentFullData
    {
        $this->customs = $customs;

        return $this;
    }

    /**
     * The structure provides complete data on the selected shipment.
     * Filled structures of this type are used to define shipments using the createShipments method.
     *
     * @throws InvalidStructureException
     *
     * @return array
     */
    public function structure(): array
    {
        $structure = [];

        if (\count($this->shipper) === 0) {
            throw new InvalidStructureException('Shipment full data shipper required');
        }

        $structure['shipper'] = $this->shipper;

        if (\count($this->receiver) === 0) {
            throw new InvalidStructureException('Shipment full data receiver required');
        }

        $structure['receiver'] = $this->receiver;

        if (\count($this->pieceList) === 0) {
            throw new InvalidStructureException('Shipment full data pieceList required');
        }

        $structure['pieceList'] = $this->pieceList;

        if (\count($this->payment) === 0) {
            throw new InvalidStructureException('Shipment full data payment required');
        }

        $structure['payment'] = $this->payment;

        if (\count($this->service) === 0) {
            throw new InvalidStructureException('Shipment full data service required');
        }

        $structure['service'] = $this->service;

        if (\strlen($this->shipmentDate) === 0) {
            throw new InvalidStructureException('Shipment full data shipmentDate required');
        }

        $structure['shipmentDate'] = $this->shipmentDate;

        if ($this->skipRestrictionCheck === true) {
            $structure['skipRestrictionCheck'] = $this->skipRestrictionCheck;
        }

        if (\strlen($this->comment) > 0) {
            $structure['comment'] = $this->comment;
        }

        if (\strlen($this->content) === 0) {
            throw new InvalidStructureException('Shipment full data content required');
        }

        $structure['content'] = $this->content;

        if (\strlen($this->reference) > 0) {
            $structure['reference'] = $this->reference;
        }

        if (\count($this->customs) > 0) {
            $structure['customs'] = $this->customs;
        }

        return [
            'item' => $structure,
        ];
    }
}
