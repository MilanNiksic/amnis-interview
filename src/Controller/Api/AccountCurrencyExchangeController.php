<?php

namespace App\Controller\Api;

use App\Dto\Request\AccountCurrencyExchangeRequest;
use App\Dto\Response\AccountCurrencyExchangeResponse;
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
            $data = json_decode($request->getContent(), true);
            
            // Denormalize JSON to Request DTO
            /** @var AccountCurrencyExchangeRequest $exchangeRequest */
            $exchangeRequest = $serializer->denormalize($data, AccountCurrencyExchangeRequest::class);
            
            // Validate Request DTO
            $violations = $validator->validate($exchangeRequest);
            if (count($violations) > 0) {
                $errors = [];
                foreach ($violations as $violation) {
                    $errors[$violation->getPropertyPath()] = $violation->getMessage();
                }
                return new JsonResponse(
                    ['errors' => $errors],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Fetch accounts
            $fromAccount = $accountRepository->find($exchangeRequest->fromAccountId);
            $toAccount = $accountRepository->find($exchangeRequest->toAccountId);
            if (!$fromAccount || !$toAccount) {
                return new JsonResponse(
                    ['error' => 'One or both accounts not found'],
                    Response::HTTP_NOT_FOUND
                );
            }

            // Validate business logic
            try {
                $exchangeManager->validateExchangeData($fromAccount, $toAccount, (float)$exchangeRequest->amount);
            } catch (\InvalidArgumentException $e) {
                return new JsonResponse(
                    ['error' => $e->getMessage()],
                    Response::HTTP_BAD_REQUEST
                );
            }

            // Execute exchange
            $exchange = $exchangeManager->executeExchange(
                $fromAccount,
                $toAccount,
                (float)$exchangeRequest->amount
            );

            // Create Response DTO
            $responseData = new AccountCurrencyExchangeResponse(
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

            return new JsonResponse(
                $serializer->normalize($responseData),
                Response::HTTP_CREATED
            );
        } catch (\Exception $e) {
            return new JsonResponse(
                ['error' => 'Exchange failed: ' . $e->getMessage()],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }
    }
}
