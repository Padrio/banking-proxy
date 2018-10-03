<?php

namespace App\Controller;

use DateTime;
use Exception;
use Padrio\BankingProxy\Transaction;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Pascal Krason <p.krason@padr.io>
 */
final class TransactionController extends AbstractController
{
    /**
     * @var Transaction
     */
    private $transaction;

    public function __construct(Transaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * @Route("/transaction", name="transaction")
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $authenticated = $this->authenticate($request);
        if($authenticated instanceof JsonResponse) {
            return $authenticated;
        }

        $accountNumber = getenv('FINTS_ACCOUNT');
        $parameters = $this->parseParameters($request);
        if ($parameters instanceof JsonResponse) {
            return $parameters;
        }

        try {
            list($from, $to) = $parameters;
            $collection = $this->transaction->getStatementCollection($accountNumber, $from, $to);
        } catch (Exception $e) {
            return $this->json(['error' => 'Could not fetch transactions: ' . $e->getMessage()], 500);
        }

        return $this->json(['transactions' => $collection->toArray()]);
    }

    private function authenticate(Request $request, string $header = 'Authorization')
    {
        if (!$request->headers->has($header)) {
            $message = sprintf('Missing `%s`-Header', $header);

            return $this->json(['error' => $message], 400);
        }

        $token = getenv('SECURITY_TOKEN');
        list(, $headerToken) = explode(' ', $request->headers->get($header));
        if ($headerToken !== $token) {
            return $this->json(['error' => 'Access denied'], 401);
        }

        return true;
    }

    private function parseParameters(Request $request, string $format = 'd.m.Y')
    {
        $query = $request->query;
        if (!$query->has('from')) {
            return $this->json(['error' => 'Missing parameter `from` '], 400);
        }

        $from = DateTime::createFromFormat($format . ' H:i:s', $query->get('from') . ' 00:00:00');
        if ($from === false) {
            return $this->json(['error' => 'Failed to parse parameter `from`. Format: ' . $format], 400);
        }

        $to = $query->get('to', 'now');
        if ($to === 'now') {
            $to = new DateTime('today 23:59:59');
        } else {
            $to = DateTime::createFromFormat($format . ' H:i:s', $query->get('to', 'now') . '23:59:59');
        }

        if ($to === false) {
            return $this->json(['error' => 'Failed to parse parameter `to`. Format: ' . $format], 400);
        }

        return [$from, $to];
    }
}
