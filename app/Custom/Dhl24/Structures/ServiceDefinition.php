<?php

namespace App\Custom\Dhl24\Structures;

use App\Custom\Dhl24\Exceptions\InvalidStructureException;

class ServiceDefinition
{
    public const PRODUCT_DOMESTIC_SHIPMENT = 'AH';

    public const PRODUCT_PREMIUM_SHIPMENT = 'PR';

    public const PRODUCT_DOMESTIC_09_SHIPMENT = '09';

    public const PRODUCT_DOMESTIC_12_SHIPMENT = '12';

    public const PRODUCT_CONNECT_SHIPMENT = 'EK';

    public const PRODUCT_INTERNATIOLAL_SHIPMENT = 'PI';

    /**
     * Dictionary value:
     *
     * AH - domestic shipment
     * PR - Premium
     * 09 - Domestic 09
     * 12 - Domestic 12
     * EK - Connect
     * PI - International shipment
     *
     * required
     *
     * @var string
     */
    private $product = '';

    /**
     * Choice of Evening Service
     *
     * @var bool
     */
    private $deliveryEvening = false;

    /**
     * Choice of Saturday delivery service
     *
     * @var bool
     */
    private $deliveryOnSaturday = false;

    /**
     * Choosing the Saturday Shipment service
     *
     * @var bool
     */
    private $pickupOnSaturday = false;

    /**
     * The choice of service is charged on delivery
     *
     * @var bool
     */
    private $collectOnDelivery = false;

    /**
     * Amount of collection in PLN, accurate to grosz, maximum PLN 11,000.
     *
     * Required, if COD is selected
     *
     * @var float
     */
    private $collectOnDeliveryValue = 0;

    /**
     * Payment type. Valid values: BANK_TRANSFER - transfer.
     *
     * Required, if COD is selected
     *
     * @var string
     */
    private $collectOnDeliveryForm = 'BANK_TRANSFER';

    /**
     * The "picking reference" field on the waybill.
     *
     * @var string
     */
    private $collectOnDeliveryReference = '';

    /**
     * Insurance service selection
     *
     * @var bool
     */
    private $insurance = false;

    /**
     * Value of the shipment to be insured
     *
     * Required, if insurance is selected
     *
     * @var float
     */
    private $insuranceValue = 0;

    /**
     * Confirmed document return service
     *
     * @var bool
     */
    private $returnOnDelivery = false;

    /**
     * Name of the return document
     *
     * @var string
     */
    private $returnOnDeliveryReference = '';

    /**
     * Delivery confirmation service selection For PI product - Recycling service selection
     *
     * @var bool
     */
    private $proofOfDelivery = false;

    /**
     * Description
     *
     * @var bool
     */
    private $selfCollect = false;

    /**
     * Delivery to a neighbor
     *
     * @var bool
     */
    private $deliveryToNeighbour = false;

    /**
     * Selection of pre-delivery information service
     *
     * @var bool
     */
    private $predeliveryInformation = false;

    /**
     * Selection of the pre-delivery service - information for the recipient about the acceptance of the package for delivery
     *
     * @var bool
     */
    private $preaviso = false;

    public function setProduct(string $product): ServiceDefinition
    {
        $this->product = $product;

        return $this;
    }

    public function setDeliveryEvening(bool $deliveryEvening): ServiceDefinition
    {
        $this->deliveryEvening = $deliveryEvening;

        return $this;
    }

    public function setDeliveryOnSaturday(bool $deliveryOnSaturday): ServiceDefinition
    {
        $this->deliveryOnSaturday = $deliveryOnSaturday;

        return $this;
    }

    public function setPickupOnSaturday(bool $pickupOnSaturday): ServiceDefinition
    {
        $this->pickupOnSaturday = $pickupOnSaturday;

        return $this;
    }

    public function setCollectOnDelivery(bool $collectOnDelivery): ServiceDefinition
    {
        $this->collectOnDelivery = $collectOnDelivery;

        return $this;
    }

    public function setCollectOnDeliveryValue(float $collectOnDeliveryValue): ServiceDefinition
    {
        $this->collectOnDeliveryValue = $collectOnDeliveryValue;

        return $this;
    }

    public function setCollectOnDeliveryForm(string $collectOnDeliveryForm): ServiceDefinition
    {
        $this->collectOnDeliveryForm = $collectOnDeliveryForm;

        return $this;
    }

    public function setCollectOnDeliveryReference(string $collectOnDeliveryReference): ServiceDefinition
    {
        $this->collectOnDeliveryReference = $collectOnDeliveryReference;

        return $this;
    }

    public function setInsurance(bool $insurance): ServiceDefinition
    {
        $this->insurance = $insurance;

        return $this;
    }

    public function setInsuranceValue(float $insuranceValue): ServiceDefinition
    {
        $this->insuranceValue = $insuranceValue;

        return $this;
    }

    public function setReturnOnDelivery(bool $returnOnDelivery): ServiceDefinition
    {
        $this->returnOnDelivery = $returnOnDelivery;

        return $this;
    }

    public function setReturnOnDeliveryReference(string $returnOnDeliveryReference): ServiceDefinition
    {
        $this->returnOnDeliveryReference = $returnOnDeliveryReference;

        return $this;
    }

    public function setProofOfDelivery(bool $proofOfDelivery): ServiceDefinition
    {
        $this->proofOfDelivery = $proofOfDelivery;

        return $this;
    }

    public function setSelfCollect(bool $selfCollect): ServiceDefinition
    {
        $this->selfCollect = $selfCollect;

        return $this;
    }

    public function setDeliveryToNeighbour(bool $deliveryToNeighbour): ServiceDefinition
    {
        $this->deliveryToNeighbour = $deliveryToNeighbour;

        return $this;
    }

    public function setPredeliveryInformation(bool $predeliveryInformation): ServiceDefinition
    {
        $this->predeliveryInformation = $predeliveryInformation;

        return $this;
    }

    public function setPreaviso(bool $preaviso): ServiceDefinition
    {
        $this->preaviso = $preaviso;

        return $this;
    }

    /**
     * In this structure, information about the selected transport service and additional services is sent.
     *
     * @throws InvalidStructureException
     *
     * @return array
     */
    public function structure(): array
    {
        $structure = [];

        if (\strlen($this->product) === 0) {
            throw new InvalidStructureException('Service definition product can not be empty');
        }

        if (!\in_array(
            $this->product,
            [
                self::PRODUCT_DOMESTIC_SHIPMENT,
                self::PRODUCT_PREMIUM_SHIPMENT,
                self::PRODUCT_DOMESTIC_09_SHIPMENT,
                self::PRODUCT_DOMESTIC_12_SHIPMENT,
                self::PRODUCT_CONNECT_SHIPMENT,
                self::PRODUCT_INTERNATIOLAL_SHIPMENT,
            ]
        )) {
            throw new InvalidStructureException('Service definition product available values is: AH, PR, 09, 12, EK, PI');
        }

        $structure['product'] = $this->product;

        $structure['deliveryEvening'] = $this->deliveryEvening;

        if ($this->deliveryOnSaturday === true) {
            $structure['deliveryOnSaturday'] = $this->deliveryOnSaturday;
        }

        if ($this->pickupOnSaturday === true) {
            $structure['pickupOnSaturday'] = $this->pickupOnSaturday;
        }

        if ($this->collectOnDelivery === true) {
            $structure['collectOnDelivery'] = $this->collectOnDelivery;

            if ($this->collectOnDeliveryValue === 0) {
                throw new InvalidStructureException('Service definition collect on delivery value required, if COD is selected');
            }

            $structure['collectOnDeliveryValue'] = $this->collectOnDeliveryValue;

            if (\strlen($this->collectOnDeliveryForm) === 0) {
                throw new InvalidStructureException('Service definition collect on delivery form required, if COD is selected');
            }

            $structure['collectOnDeliveryForm'] = $this->collectOnDeliveryForm;
        }

        if (\strlen($this->collectOnDeliveryReference) > 0) {
            $structure['collectOnDeliveryReference'] = $this->collectOnDeliveryReference;
        }

        if ($this->insurance === true) {
            $structure['insurance'] = $this->insurance;

            if ($this->insuranceValue === 0) {
                throw new InvalidStructureException('Service definition insurance value required, if insurance is selected');
            }

            $structure['insuranceValue'] = $this->insuranceValue;
        }

        if ($this->returnOnDelivery === true) {
            $structure['returnOnDelivery'] = $this->returnOnDelivery;
        }

        if ($this->proofOfDelivery === true) {
            $structure['proofOfDelivery'] = $this->proofOfDelivery;
        }

        if ($this->selfCollect === true) {
            $structure['selfCollect'] = $this->selfCollect;
        }

        if ($this->deliveryToNeighbour === true) {
            $structure['deliveryToNeighbour'] = $this->deliveryToNeighbour;
        }

        if ($this->predeliveryInformation === true) {
            $structure['predeliveryInformation'] = $this->predeliveryInformation;
        }

        if ($this->preaviso === true) {
            $structure['preaviso'] = $this->preaviso;
        }

        return $structure;
    }
}
