<?php

declare(strict_types=1);

namespace App\Model;

use Fhp\Model\StatementOfAccount\Statement as FhpStatement;

/**
 * @author Pascal Krason <p.krason@padr.io>
 * This class will be used in future to unify the balance in cent because floats sucks
 * I've created it to make the change without BC breaks.
 */
final class Balance
{
    /**
     * @var float
     */
    public $start = 0.0;

    public function __construct(float $start = 0.0)
    {
        $this->start = $start;
    }

    public static function createFromFhpStatement(FhpStatement $statement): self
    {
        return new self($statement->getStartBalance());
    }

    public function toArray(): array
    {
        return [
            'start' => $this->start,
        ];
    }
}