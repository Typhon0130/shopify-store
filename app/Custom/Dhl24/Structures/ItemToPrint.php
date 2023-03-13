<?php

namespace App\Custom\Dhl24\Structures;

use App\Custom\Dhl24\Exceptions\InvalidStructureException;

class ItemToPrint
{
    public const LABEL_TYPE_LP = 'LP';

    public const LABEL_TYPE_BLP = 'BLP';

    public const LABEL_TYPE_LBLP = 'LBLP';

    public const LABEL_TYPE_ZBLP = 'ZBLP';

    /**
     * Dictionary value - one of the values:
     * LP - consignment note,
     * BLP - BLP label,
     * LBLP - BLP label in PDF A4 format,
     * ZBLP - BLP label in format for Zebra printers
     *
     * required
     *
     * @var string
     */
    private $labelType = '';

    /**
     * The shipment number for which we want to download a label
     *
     * required
     *
     * @var string
     */
    private $shipmentId = '';

    /**
     *  Set labelType
     *
     * @param string $labelType
     *
     * @return ItemToPrint
     */
    public function setLabelType(string $labelType): ItemToPrint
    {
        $this->labelType = $labelType;

        return $this;
    }

    /**
     * Set shipment id
     *
     * @param string $shipmentId
     *
     * @return ItemToPrint
     */
    public function setShipmentId(string $shipmentId): ItemToPrint
    {
        $this->shipmentId = $shipmentId;

        return $this;
    }

    /**
     * The structure defines the type of label that the user can retrieve using the getLabels method.
     *
     * @throws InvalidStructureException
     *
     * @return array
     */
    public function structure(): array
    {
        if (\strlen($this->labelType) === 0) {
            throw new InvalidStructureException('Item to print labelType required');
        }

        if (\strlen($this->shipmentId) === 0) {
            throw new InvalidStructureException('Item to print shipmentId required');
        }

        if (!in_array($this->labelType, [self::LABEL_TYPE_LP, self::LABEL_TYPE_BLP, self::LABEL_TYPE_LBLP, self::LABEL_TYPE_ZBLP])) {
            throw new InvalidStructureException('Item to print invalid labelType');
        }

        $structure = [];

        $structure['labelType'] = $this->labelType;

        $structure['shipmentId'] = $this->shipmentId;

        return $structure;
    }
}
