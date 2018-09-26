<?php

declare(strict_types=1);

namespace App\Model;

use DateTimeImmutable;
use DateTimeInterface;
use Fhp\Model\StatementOfAccount\Statement as FhpStatement;

/**
 * @author Pascal Krason <p.krason@padr.io>
 */
final class Statement
{
    const TYPE_CREDIT = 'credit';
    const TYPE_DEBIT = 'debit';

    /**
     * @var Transaction[]
     */
    public $transactions = [];

    /**
     * @var Balance
     */
    public $balance;

    /**
     * @var string
     */
    public $type;

    /**
     * @var DateTimeImmutable
     */
    public $date;

    public static function createFromFinTsStatement(FhpStatement $statement): self
    {
        $instance = new self();
        $instance->balance = Balance::createFromFhpStatement($statement);
        $instance->date = DateTimeImmutable::createFromMutable($statement->getDate());
        $instance->type = $statement->getCreditDebit();

        foreach($statement->getTransactions() as $transaction) {
            $instance->transactions[] = Transaction::createFromFhpTransaction($transaction);
        }

        return $instance;
    }

    public function toArray(): array
    {
        $transactions = array_map(function(Transaction $transaction){
            return $transaction->toArray();
        }, $this->transactions);

        return [
            'transactions' => $transactions,
            'balance' => $this->balance->toArray(),
            'type' => $this->type,
            'date' => $this->date->format(DateTimeInterface::ISO8601),
        ];
    }
}