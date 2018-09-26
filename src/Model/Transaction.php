<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;
use DateTimeInterface;
use Fhp\Model\StatementOfAccount\Transaction as FhpTransaction;

/**
 * @author Pascal Krason <p.krason@padr.io>
 */
final class Transaction
{
    const TYPE_CREDIT = 'credit';
    const TYPE_DEBIT = 'debit';

    /**
     * @var DateTimeImmutable
     */
    public $bookingDate;

    /**
     * @var DateTimeImmutable
     */
    public $valueDate;

    /**
     * @var float
     */
    public $amount;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $bookingText;

    /**
     * @var string
     */
    public $description;

    /**
     * @var string
     */
    public $bankCode;

    /**
     * @var string
     */
    public $accountNumber;

    /**
     * @var string
     */
    public $name;

    public static function createFromFhpTransaction(FhpTransaction $transaction): self
    {
        $instance = new self();
        $instance->bookingDate = DateTimeImmutable::createFromMutable($transaction->getBookingDate());
        $instance->valueDate = DateTimeImmutable::createFromMutable($transaction->getValutaDate());
        $instance->amount = $transaction->getAmount();
        $instance->type = $transaction->getCreditDebit();
        $instance->bookingText = $transaction->getBookingText();
        $instance->description = $transaction->getDescription1() . PHP_EOL . $transaction->getDescription2();
        $instance->bankCode = $transaction->getBankCode();
        $instance->accountNumber = $transaction->getAccountNumber();
        $instance->name = $transaction->getName();
        return $instance;
    }

    public function toArray(): array
    {
        return [
            'bookingDate' => $this->bookingDate->format(DateTimeInterface::ISO8601),
            'valueDate' => $this->valueDate->format(DateTimeInterface::ISO8601),
            'amount' => $this->amount,
            'type' => $this->type,
            'bookingText' => $this->bookingText,
            'description' => $this->description,
            'bankCode' => $this->bankCode,
            'accountNumber' => $this->accountNumber,
            'name' => $this->name,
        ];
    }
}