<?php

namespace App\Controller\Api;

use App\Dto\Request\AccountCurrencyExchangeRequest;
use App\Dto\Response\AccountCurrencyExchangeResponse;
use App\Entity\Account;
use App\Entity\Exchange;
use App\Repository\AccountRepository;
use App\Service\ExchangeManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class AccountCurrencyExchangeController extends AbstractController
{
    #[Route('/api/account-currency-exchanges', name: 'api_account_currency_exchange_new', methods: ['POST'])]
    public function execute(
        Request $request,
        AccountRepository $accountRepository,
        ExchangeManager $exchangeManager,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ): JsonResponse {
        try {
            $exchangeRequest = $this->parseRequest($request, $serializer);
            $this->validateRequest($exchangeRequest, $validator);

            [$fromAccount, $toAccount] = $this->retrieveAccounts($exchangeRequest, $accountRepository);

            $exchange = $exchangeManager->executeExchange(
                $fromAccount,
                $toAccount,
                (float)$exchangeRequest->amount
            );

            return new JsonResponse(
                $serializer->normalize($this->buildResponse($exchange, $fromAccount, $toAccount)),
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'Exchange failed: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }

    private function parseRequest(Request $request, SerializerInterface $serializer): AccountCurrencyExchangeRequest
    {
        $data = json_decode($request->getContent(), true);

        if ($data === null) {
            throw new \InvalidArgumentException('Invalid JSON payload');
        }

        /** @var AccountCurrencyExchangeRequest $exchangeRequest */
        return $serializer->denormalize($data, AccountCurrencyExchangeRequest::class);
    }

    private function validateRequest(AccountCurrencyExchangeRequest $exchangeRequest, ValidatorInterface $validator): void
    {
        $violations = $validator->validate($exchangeRequest);

        if (count($violations) > 0) {
            $errors = [];
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }
            throw new \InvalidArgumentException(json_encode($errors));
        }
    }

    private function retrieveAccounts(
        AccountCurrencyExchangeRequest $exchangeRequest,
        AccountRepository $accountRepository
    ): array {
        $fromAccount = $accountRepository->find($exchangeRequest->fromAccountId);
        $toAccount = $accountRepository->find($exchangeRequest->toAccountId);

        if (!$fromAccount || !$toAccount) {
            throw new \RuntimeException('One or both accounts not found');
        }

        return [$fromAccount, $toAccount];
    }

    private function buildResponse(Exchange $exchange, Account $fromAccount, Account $toAccount): AccountCurrencyExchangeResponse
    {
        return new AccountCurrencyExchangeResponse(
            id: $exchange->getId(),
            fromAccountId: $fromAccount->getId(),
            toAccountId: $toAccount->getId(),
            fromCurrency: $fromAccount->getCurrency()->getCode(),
            toCurrency: $toAccount->getCurrency()->getCode(),
            fromAmount: $exchange->getFromAmount() / $fromAccount->getCurrencyScale(),
            toAmount: $exchange->getToAmount() / $toAccount->getCurrencyScale(),
            exchangeRate: $exchange->getExchangeRate(),
            createdAt: $exchange->getCreatedAt()->format('Y-m-d H:i:s')
        );
    }
}
