<?php

namespace App\Custom\Dhl24\Structures;

use App\Custom\Dhl24\Exceptions\InvalidStructureException;
use App\Custom\Dhl24\Utils;

class ReceiverAddress
{
    public const ADDRESS_TYPE_B = 'B';

    public const ADDRESS_TYPE_C = 'C';

    /**
     * Address type (B/C)
     * (1) required
     *
     * @var string
     */
    private $addressType = '';

    /**
     * Country code
     * (60) required
     *
     * @var string
     */
    private $country = '';

    /**
     * Delivery to DHL Parcelstation
     *
     * @var bool
     */
    private $isPackstation = false;

    /**
     * Delivery to DHL Parcelshop
     *
     * @var bool
     */
    private $isPostfiliale = false;

    /**
     * Customer number - required only for DE when delivering to a parcel locker
     * (10)
     *
     * @var string
     */
    private $postnummer = '';

    /**
     * Company name or first and last name
     * (60) required
     *
     * @var string
     */
    private $name = '';

    /**
     * Zip code, no hyphen
     * (10) required
     *
     * @var string
     */
    private $postalCode = '';

    /**
     * City name
     * (17) required
     *
     * @var string
     */
    private $city = '';

    /**
     * Street
     * (35) requred
     *
     * @var string
     */
    private $street = '';

    /**
     * House number
     * The sum of characters in fields "apartmentNumber" and "houseNumber" cannot exceed 15 characters
     * (10) required
     *
     * @var string
     */
    private $houseNumber = '';

    /**
     * Apartment number The sum of characters in "apartmentNumber" and "houseNumber" fields cannot exceed 15 characters (10)
     *
     * @var string
     */
    private $apartmentNumber = '';

    /**
     * Name of the contact person
     * (60)
     *
     * @var string
     */
    private $contactPerson = '';

    /**
     * Contact phone number
     * (20)
     *
     * @var string
     */
    private $contactPhone = '';

    /**
     * Contact email
     * (60)
     *
     * @var string
     */
    private $contactEmail = '';

    public function setAddressType(string $addressType): ReceiverAddress
    {
        $this->addressType = $addressType;

        return $this;
    }

    public function setCountry(string $country): ReceiverAddress
    {
        $this->country = $country;

        return $this;
    }

    public function setIsPackstation(bool $isPackstation): ReceiverAddress
    {
        $this->isPackstation = $isPackstation;

        return $this;
    }

    public function setIsPostfiliale(bool $isPostfiliale): ReceiverAddress
    {
        $this->isPostfiliale = $isPostfiliale;

        return $this;
    }

    public function setPostnummer(bool $postnummer): ReceiverAddress
    {
        $this->postnummer = $postnummer;

        return $this;
    }

    public function setName(string $name): ReceiverAddress
    {
        $this->name = $name;

        return $this;
    }

    public function setPostalCode(string $postalCode): ReceiverAddress
    {
        $this->postalCode = Utils::onlyNumbers($postalCode);

        return $this;
    }

    public function setCity(string $city): ReceiverAddress
    {
        $this->city = $city;

        return $this;
    }

    public function setStreet(string $street): ReceiverAddress
    {
        $this->street = $street;

        return $this;
    }

    public function setHouseNumber(string $houseNumber): ReceiverAddress
    {
        $this->houseNumber = $houseNumber;

        return $this;
    }

    public function setApartmentNumber(string $apartmentNumber): ReceiverAddress
    {
        $this->apartmentNumber = $apartmentNumber;

        return $this;
    }

    public function setContactPerson(string $contactPerson): ReceiverAddress
    {
        $this->contactPerson = $contactPerson;

        return $this;
    }

    public function setContactPhone(string $contactPhone): ReceiverAddress
    {
        $this->contactPhone = Utils::onlyNumbers($contactPhone);

        return $this;
    }

    public function setContactEmail(string $contactEmail): ReceiverAddress
    {
        $this->contactEmail = $contactEmail;

        return $this;
    }

    /**
     * Get address structure
     *
     * The described structure is the basic object used to transfer address data.
     * It is used, for example, when passing shipping addresses and delivery addresses in the createShipments method.
     *
     * @throws InvalidStructureException
     *
     * @return array
     */
    public function structure(): array
    {
        $structure = [];

        if (\strlen($this->addressType) === 0) {
            throw new InvalidStructureException('Receiver address type required');
        }

        if (!\in_array(
            $this->addressType,
            [
                self::ADDRESS_TYPE_B,
                self::ADDRESS_TYPE_C,
            ]
        )) {
            throw new InvalidStructureException('Receiver address type available values is: B, C');
        }

        $structure['addressType'] = $this->addressType;

        if (\strlen($this->country) === 0) {
            throw new InvalidStructureException('Receiver address country required');
        }

        $structure['country'] = $this->country;

        if ($this->isPackstation === true) {
            $structure['isPackstation'] = $this->isPackstation;
        }

        if ($this->isPostfiliale === true) {
            $structure['isPostfiliale'] = $this->isPostfiliale;
        }

        if (\strlen($this->postnummer) > 0) {
            $structure['postnummer'] = $this->postnummer;
        }

        if (\strlen($this->name) === 0) {
            throw new InvalidStructureException('Receiver address name required');
        }

        $structure['name'] = $this->name;

        if (\strlen($this->postalCode) === 0) {
            throw new InvalidStructureException('Receiver address postal code required');
        }

        $structure['postalCode'] = $this->postalCode;

        if (\strlen($this->city) === 0) {
            throw new InvalidStructureException('Receiver address city required');
        }

        $structure['city'] = $this->city;

        if (\strlen($this->street) === 0) {
            throw new InvalidStructureException('Receiver address street required');
        }

        $structure['street'] = $this->street;

        if (\strlen($this->houseNumber) === 0) {
            throw new InvalidStructureException('Receiver address house number required');
        }

        $structure['houseNumber'] = $this->houseNumber;

        if (\strlen($this->apartmentNumber) > 0) {
            $structure['apartmentNumber'] = $this->apartmentNumber;
        }

        if (\strlen($this->contactPerson) > 0) {
            $structure['contactPerson'] = $this->contactPerson;
        }

        if (\strlen($this->contactPhone) > 0) {
            $structure['contactPhone'] = $this->contactPhone;
        }

        if (\strlen($this->contactEmail) > 0) {
            $structure['contactEmail'] = $this->contactEmail;
        }

        return $structure;
    }
}
