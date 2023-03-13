<?php

namespace App\Custom\Dhl24\Structures;

use App\Custom\Dhl24\Exceptions\InvalidStructureException;
use App\Custom\Dhl24\Utils;

class Address
{
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

    public function setName(string $name): Address
    {
        $this->name = $name;

        return $this;
    }

    public function setPostalCode(string $postalCode): Address
    {
        $this->postalCode = Utils::onlyNumbers($postalCode);

        return $this;
    }

    public function setCity(string $city): Address
    {
        $this->city = $city;

        return $this;

    }

    public function setStreet(string $street): Address
    {
        $this->street = $street;

        return $this;
    }

    public function setHouseNumber(string $houseNumber): Address
    {
        $this->houseNumber = $houseNumber;

        return $this;
    }

    public function setApartmentNumber(string $apartmentNumber): Address
    {
        $this->apartmentNumber = $apartmentNumber;

        return $this;
    }

    public function setContactPerson(string $contactPerson): Address
    {
        $this->contactPerson = $contactPerson;

        return $this;
    }

    public function setContactPhone(string $contactPhone): Address
    {
        $this->contactPhone = Utils::onlyNumbers($contactPhone);

        return $this;
    }

    public function setContactEmail(string $contactEmail): Address
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

        if (\strlen($this->name) === 0) {
            throw new InvalidStructureException('Address name required');
        }

        $structure['name'] = $this->name;

        if (\strlen($this->postalCode) === 0) {
            throw new InvalidStructureException('Address postal code required');
        }

        $structure['postalCode'] = $this->postalCode;

        if (\strlen($this->city) === 0) {
            throw new InvalidStructureException('Address city required');
        }

        $structure['city'] = $this->city;

        if (\strlen($this->street) === 0) {
            throw new InvalidStructureException('Address street required');
        }

        $structure['street'] = $this->street;

        if (\strlen($this->houseNumber) === 0) {
            throw new InvalidStructureException('Address house number required');
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
