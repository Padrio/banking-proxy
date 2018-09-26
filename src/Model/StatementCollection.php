<?php

declare(strict_types=1);

namespace App\Model;

use Fhp\Model\StatementOfAccount\StatementOfAccount as FhpStatementCollection;
use Fhp\Model\StatementOfAccount\Statement as FhpStatement;

/**
 * @author Pascal Krason <p.krason@padr.io>
 */
final class StatementCollection
{
    /**
     * @var Statement[]
     */
    private $statements = [];

    public static function createFromFhpStatement(FhpStatementCollection $statementOfAccount): self
    {
        $instance = new self();

        $instance->statements = array_map(function(FhpStatement $statement) {
            return Statement::createFromFinTsStatement($statement);
        }, $statementOfAccount->getStatements());

        return $instance;
    }

    public function toArray(): array
    {
        $statements = array_map(function(Statement $statement){
            return $statement->toArray();
        }, $this->statements);

        return [
            'statements' => $statements,
        ];
    }
}