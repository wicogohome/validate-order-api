<?php

namespace App\Services;

use App\Repositories\CurrencyRepository;

class CurrencyService
{
    private string $defaultCurrency;

    private int $defaultScale;

    public function __construct(
        private CurrencyRepository $currencyRepository,
        string $defaultCurrency = 'TWD',
        int $defaultScale = 2,
    ) {
        $this->defaultCurrency = $defaultCurrency;
        $this->defaultScale = $defaultScale;

        bcscale($this->defaultScale);
    }

    public function convertToDefaultCurrency(float $price, string $fromCurrency): float
    {
        if ($fromCurrency !== $this->defaultCurrency) {
            $rate = $this->currencyRepository->getExchangeRate($fromCurrency, $this->defaultCurrency);
            $price = bcmul($price, $rate);
        }

        return round((float) $price, 0);
    }

    public function getDefaultCurrency(): string
    {
        return $this->defaultCurrency;
    }
}
