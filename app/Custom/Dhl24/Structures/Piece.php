<?php

namespace App\Custom\Dhl24\Structures;

use App\Custom\Dhl24\Exceptions\InvalidStructureException;

class Piece
{
    public const TYPE_ENVELOPE = 'ENVELOPE';

    public const TYPE_PACKAGE = 'PACKAGE';

    public const TYPE_PALLET = 'PALLET';

    /**
     * One of the values: "ENVELOPE", "PACKAGE", "PALLET"
     *
     *  required
     *
     * @var string
     */
    private $type = '';

    /**
     * Width in centimeters
     * 
     * required when type other than "ENVELOPE"
     *
     * @var int
     */
    private $width = 0;

    /**
     * Height in centimeters
     * 
     * required when type other than "ENVELOPE"
     *
     * @var int
     */
    private $height = 0;

    /**
     * Length in centimeters
     * 
     * required when type other than "ENVELOPE"
     *
     * @var int
     */
    private $length = 0;

    /**
     * Weight in kilograms
     * 
     * required when type other than "ENVELOPE"
     *
     * @var int
     */
    private $weight = 0;

    /**
     * Number of packages
     *
     * required
     * 
     * @var int
     */
    private $quantity = 0;

    /**
     * Is the package non-standard
     * (as defined in the price list)
     *
     * @var bool
     */
    private $nonStandard = false;

    /**
     * Whether euro pallets to be returned
     * (can only be selected with type = "PALLET")
     *
     * @var bool
     */
    private $euroReturn = false;

    /**
     * BLP identifier - for customers printing labels of this type, who keep their own parcel numbering
     *
     * @var string
     */
    private $blpPieceId = '';

    public function setType(string $type): Piece
    {
        $this->type = $type;

        return $this;
    }

    public function setWidth(int $width): Piece
    {
        $this->width = $width;

        return $this;
    }

    public function setHeight(int $height): Piece
    {
        $this->height = $height;

        return $this;
    }

    public function setLength(int $length): Piece
    {
        $this->length = $length;

        return $this;
    }

    public function setWeight(int $weight): Piece
    {
        $this->weight = $weight;

        return $this;
    }

    public function setQuantity(int $quantity): Piece
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function setNonStandard(bool $nonStandard): Piece
    {
        $this->nonStandard = $nonStandard;

        return $this;
    }

    public function setEuroReturn(bool $euroReturn): Piece
    {
        $this->euroReturn = $euroReturn;

        return $this;
    }

    public function setBlpPieceId(string $blpPieceId): Piece
    {
        $this->blpPieceId = $blpPieceId;

        return $this;
    }

    /**
     * The structure describes the physical parameters of the packages.
     * It is used in the createShipments method.
     *
     * @throws InvalidStructureException
     *
     * @return array
     */
    public function structure(): array
    {
        $structure = [];

        if (\strlen($this->type) === 0) {
            throw new InvalidStructureException('Piece type can not be empty');
        }

        if (!\in_array(
            $this->type,
            [
                self::TYPE_ENVELOPE,
                self::TYPE_PACKAGE,
                self::TYPE_PALLET,
            ]
        )) {
            throw new InvalidStructureException('Piece type available values is: ENVELOPE, PACKAGE, PALLET');
        }

        $structure['type'] = $this->type;

        if ($this->type !== self::TYPE_ENVELOPE) {
            if ($this->width === 0) {
                throw new InvalidStructureException('Piece width required when type other than "ENVELOPE"');
            }

            if ($this->height === 0) {
                throw new InvalidStructureException('Piece height required when type other than "ENVELOPE"');
            }

            if ($this->length === 0) {
                throw new InvalidStructureException('Piece length required when type other than "ENVELOPE"');
            }

            if ($this->weight === 0) {
                throw new InvalidStructureException('Piece weight required when type other than "ENVELOPE"');
            }
        }

        if ($this->width !== 0) {
            $structure['width'] = $this->width;
        }

        if ($this->height !== 0) {
            $structure['height'] = $this->height;
        }

        if ($this->length !== 0) {
            $structure['length'] = $this->length;
        }

        if ($this->weight !== 0) {
            $structure['weight'] = $this->weight;
        }

        if ($this->quantity !== 0) {
            $structure['quantity'] = $this->quantity;
        } else {
            throw new InvalidStructureException('Piece quantity required');
        }

        $structure['nonStandard'] = $this->nonStandard;
        
        if ($this->euroReturn === true && $this->type === self::TYPE_PALLET) {
            $structure['euroReturn'] = $this->euroReturn;
        }

        if (\strlen($this->blpPieceId) > 0) {
            $structure['blpPieceId'] = $this->blpPieceId;
        }

        return $structure;
    }
}
