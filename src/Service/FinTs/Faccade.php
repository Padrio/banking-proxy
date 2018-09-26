<?php

declare(strict_types=1);

namespace App\Service\FinTs;

use App\Model\StatementCollection;
use DateTime;
use Exception;
use Fhp\Adapter\Exception\AdapterException;
use Fhp\Adapter\Exception\CurlException;
use Fhp\FinTs;
use Fhp\Model\Account;
use Fhp\Model\SEPAAccount;

final class Faccade
{
    /**
     * @var FinTs
     */
    private $finTs;

    public function __construct(FinTs $finTs)
    {
        $this->finTs = $finTs;
    }

    public function getFinTs(): FinTs
    {
        return $this->finTs;
    }

    public function setFinTs(FinTs $finTs): void
    {
        $this->finTs = $finTs;
    }

    public function findAccountByNumber(int $accountNumber): ?SEPAAccount
    {
        static $cache = [];
        if(isset($cache[$accountNumber])) {
            return $cache[$accountNumber];
        }

        try {
            $accounts = $this->getFinTs()->getSEPAAccounts();
        } catch (CurlException | AdapterException $e) {
            // @Todo: Logging

            return null;
        }

        foreach($accounts as $account) {
            if($account->getAccountNumber() == $accountNumber) {
                return $cache[$accountNumber] = $account;
            }
        }

        return null;
    }

    /**
     * @param int      $accountNumber
     * @param DateTime $from
     * @param DateTime $to
     *
     * @return StatementCollection
     * @throws Exception
     */
    public function getTransactions(int $accountNumber, DateTime $from, DateTime $to): StatementCollection
    {
        $account = $this->findAccountByNumber($accountNumber);

        if($account === null) {
            throw new Exception('Could not find account '. $accountNumber);
        }

        $statement = $this->getFinTs()->getStatementOfAccount($account, $from, $to);
        return StatementCollection::createFromFhpStatement($statement);
    }

}