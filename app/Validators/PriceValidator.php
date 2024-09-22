<?php

namespace App\Validators;

use App\Services\CurrencyService;
use App\Exceptions\ValidatorException;

class PriceValidator implements ValidatorInterface
{
    private float $maxPrice;

    public function __construct(private CurrencyService $currencyService, float $maxPrice = 2000)
    {
        $this->maxPrice = $maxPrice;
    }

    public function validate(array $data): bool
    {
        $this->validateInput($data);

        $price = $data['price'];
        $currency = $data['currency'];
        $this->validatePriceNotOver($price, $currency);

        return true;
    }

    private function validateInput(array $data): void
    {
        if (! isset($data['price']) || ! is_numeric($data['price'])) {
            throw new ValidatorException('Price is required and must be numeric');
        }

        if (! isset($data['currency']) || ! is_string($data['currency'])) {
            throw new ValidatorException('Currency is required and must be a string');
        }
    }

    private function validatePriceNotOver(float $price, string $currency): void
    {
        $priceInDefaultCurrency = $this->currencyService->convertToDefaultCurrency($price, $currency);

        if ($priceInDefaultCurrency > $this->maxPrice) {
            throw new ValidatorException('Price is over '.$this->maxPrice);
        }
    }
}
