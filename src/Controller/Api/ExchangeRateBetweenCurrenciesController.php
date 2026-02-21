<?php

namespace App\Controller\Api;

use App\Repository\CurrencyExchangeRateRepository;
use App\Entity\Currency;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class ExchangeRateBetweenCurrenciesController extends AbstractController
{
    public function __construct(
        private CurrencyExchangeRateRepository $currencyExchangeRateRepo,
    ) {}

    public function __invoke(Currency $fromCurrency, Currency $toCurrency)
    {
        $exchangeRate = $this->currencyExchangeRateRepo->findOneBy([
            'fromCurrency' => $fromCurrency,
            'toCurrency' => $toCurrency
        ]);
    
        return $exchangeRate;
    }
}