<?php

namespace App\Custom\Dhl24\Structures;

use App\Custom\Dhl24\Exceptions\InvalidStructureException;

class PaymentData
{
    public const PAYMENT_METHOD_CASH = 'CASH';

    public const PAYMENT_METHOD_BANK_TRANSFER = 'BANK_TRANSFER';

    public const PAYER_TYPE_SHIPPER = 'SHIPPER';

    public const PAYER_TYPE_RECEIVER = 'RECEIVER';

    public const PAYER_TYPE_USER = 'USER';

    /**
     * Dictionary value:
     * CASH - cash
     * BANK_TRANSFER - transfer
     * required
     *
     * @var string
     */
    private $paymentMethod = '';

    /**
     * Dictionary value:
     * SHIPPER - payer sender
     * RECEIVER - payer payee
     * USER - third party payer
     *
     * @var string
     */
    private $payerType = '';

    /**
     * The payer's customer number (SAP) required for payerType is SHIPPER or USER
     *
     * @var string
     */
    private $accountNumber = '';

    /**
     * The MPK field on the consignment note
     *
     * @var string
     */
    private $costsCenter = '';

    public function setPaymentMethod(string $paymentMethod): PaymentData
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function setPayerType(string $payerType): PaymentData
    {
        $this->payerType = $payerType;

        return $this;
    }

    public function setAccountNumber(string $accountNumber): PaymentData
    {
        $this->accountNumber = $accountNumber;

        return $this;
    }

    public function setCostsCenter(string $costsCenter): PaymentData
    {
        $this->costsCenter = $costsCenter;

        return $this;
    }

    /**
     * The structure contains information about the selected form of payment and the payer.
     *
     * Available payer combinations and payment methods:
     * USER | BANK_TRANSFER | Shipment of the "third party" type (if allowed by the assigned SAP number)
     * SHIPPER | BANK_TRANSFER | Pays the sender by bank transfer
     * RECEIVER | CASH | The recipient pays in cash
     *
     * @throws InvalidStructureException
     *
     * @return array
     */
    public function structure(): array
    {
        $structure = [];

        if (\strlen($this->paymentMethod) === 0) {
            throw new InvalidStructureException('Payment data payment method can not be empty');
        }

        if (!\in_array(
            $this->paymentMethod,
            [
                self::PAYMENT_METHOD_CASH,
                self::PAYMENT_METHOD_BANK_TRANSFER,
            ]
        )) {
            throw new InvalidStructureException('Payment data payment method available values is: CASH, BANK_TRANSFER');
        }

        $structure['paymentMethod'] = $this->paymentMethod;

        if (\strlen($this->payerType) === 0) {
            throw new InvalidStructureException('Payment data payer type can not be empty');
        }

        if (!\in_array(
            $this->payerType,
            [
                self::PAYER_TYPE_SHIPPER,
                self::PAYER_TYPE_RECEIVER,
                self::PAYER_TYPE_USER,
            ]
        )) {
            throw new InvalidStructureException('Payment data payer type available values is: SHIPPER, RECEIVER, USER');
        }

        $structure['payerType'] = $this->payerType;

        if (\in_array($this->payerType, [self::PAYER_TYPE_SHIPPER, self::PAYER_TYPE_USER])) {
            if (\strlen($this->accountNumber) === 0) {
                throw new InvalidStructureException('Payment data account number required for payerType is SHIPPER or USER');
            }
        }

        if (\strlen($this->accountNumber) > 0) {
            $structure['accountNumber'] = $this->accountNumber;
        }

        if (\strlen($this->costsCenter) > 0) {
            $structure['costsCenter'] = $this->costsCenter;
        }

        return $structure;
    }
}
