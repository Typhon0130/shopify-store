<?php

namespace App\Custom\Dhl24\Structures;

use App\Custom\Dhl24\Exceptions\InvalidStructureException;

class ItemToPrintResponse
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
     * Label binary data (Base64 encoded)
     *
     * required
     *
     * @var string
     */
    private $labelData = '';

    /**
     * The mime type of the label being sent
     *
     * required
     *
     * @var string
     */
    private $labelMimeType = '';

    /**
     * Customs document type CN23 - returned in the case of simplified customs clearance
     *
     * required (at customs)
     *
     * @var string
     */
    private $cn23MimeType = '';

    /**
     * CN23 (base64) customs document content - returned in case of simplified customs clearance
     *
     * required (at customs)
     *
     * @var string
     */
    private $cn23Content = '';

    /**
     * Document type proforma invoice - returned in case of simplified customs clearance
     *
     * required (at customs)
     *
     * @var string
     */
    private $fvProformaMimeType = '';

    /**
     * Document content proforma invoice (base64) - returned in case of simplified customs clearance
     *
     * required (at customs)
     *
     * @var string
     */
    private $fvProformaData = '';

    /**
     * Generated invoice number - if the invoice number is not provided in the input parameters
     *
     * required (at customs)
     *
     * @var string
     */
    private $fvProformaNumer = '';

    /**
     *  Set labelType
     *
     * @param string $labelType
     *
     * @return ItemToPrint
     */
    public function setLabelType(string $labelType): ItemToPrintResponse
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
    public function setShipmentId(string $shipmentId): ItemToPrintResponse
    {
        $this->shipmentId = $shipmentId;

        return $this;
    }

    public function setLabelData(string $labelData): ItemToPrintResponse
    {
        $this->labelData = $labelData;

        return $this;
    }

    public function setLabelMimeType(string $labelMimeType): ItemToPrintResponse
    {
        $this->labelMimeType = $labelMimeType;

        return $this;
    }

    public function setCn23MimeType(?string $cn23MimeType): ItemToPrintResponse
    {
        $this->cn23MimeType = $cn23MimeType;

        return $this;
    }

    public function setCn23Content(?string $cn23Content): ItemToPrintResponse
    {
        $this->cn23Content = $cn23Content;

        return $this;
    }

    public function setFvProformaMimeType(?string $fvProformaMimeType): ItemToPrintResponse
    {
        $this->fvProformaMimeType = $fvProformaMimeType;

        return $this;
    }

    public function setFvProformaData(?string $fvProformaData): ItemToPrintResponse
    {
        $this->fvProformaData = $fvProformaData;

        return $this;
    }

    public function setFvProformaNumer(?string $fvProformaNumer): ItemToPrintResponse
    {
        $this->fvProformaNumer = $fvProformaNumer;

        return $this;
    }

    public function fromResponse(object $getLabelsResult): array
    {
        $item = $getLabelsResult->item;

        if (\is_array($item)) {
            $items = [];

            foreach ($item as $element) {
                $items[] = $this->structureFromObject($element);
            }

            return $items;
        }

        return $this->structureFromObject($item);
    }

    public function structureFromObject(object $item): array
    {
        return $this
            ->setLabelType($item->labelType)
            ->setShipmentId($item->shipmentId)
            ->setLabelData($item->labelData)
            ->setLabelMimeType($item->labelMimeType)
            ->setCn23MimeType($item->cn23MimeType)
            ->setCn23Content($item->cn23Data)
            ->setFvProformaMimeType($item->fvProformaMimeType)
            ->setFvProformaData($item->fvProformaData)
            ->setFvProformaNumer($item->fvProformaNumer)
            ->structure();
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
            throw new InvalidStructureException('Item to print response labelType required');
        }

        if (\strlen($this->shipmentId) === 0) {
            throw new InvalidStructureException('Item to print response shipmentId required');
        }

        if (\strlen($this->labelData) === 0) {
            throw new InvalidStructureException('Item to print response labelData required');
        }

        if (\strlen($this->labelMimeType) === 0) {
            throw new InvalidStructureException('Item to print response labelMimeType required');
        }

        if (!in_array($this->labelType, [self::LABEL_TYPE_LP, self::LABEL_TYPE_BLP, self::LABEL_TYPE_LBLP, self::LABEL_TYPE_ZBLP])) {
            throw new InvalidStructureException('Item to print invalid labelType');
        }

        $structure = [];

        $structure['labelType'] = $this->labelType;

        $structure['shipmentId'] = $this->shipmentId;

        $structure['labelData'] = $this->labelData;

        $structure['labelMimeType'] = $this->labelMimeType;

        if (\strlen($this->cn23MimeType) > 0) {
            $structure['cn23MimeType'] = $this->cn23MimeType;
        }

        if (\strlen($this->cn23Content) > 0) {
            $structure['cn23Content'] = $this->cn23Content;
        }

        if (\strlen($this->fvProformaMimeType) > 0) {
            $structure['fvProformaMimeType'] = $this->fvProformaMimeType;
        }

        if (\strlen($this->fvProformaData) > 0) {
            $structure['fvProformaData'] = $this->fvProformaData;
        }

        if (\strlen($this->fvProformaNumer) > 0) {
            $structure['fvProformaNumer'] = $this->fvProformaNumer;
        }

        return $structure;
    }
}
