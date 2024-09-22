<?php

namespace App\Validators;

use App\Exceptions\ValidatorException;
use App\Repositories\CurrencyRepository;

class CurrencyValidator implements ValidatorInterface
{
    public function __construct(private CurrencyRepository $currencyRepository) {}

    public function validate(array $data): bool
    {
        if (! isset($data['currency']) || ! is_string($data['currency'])) {
            throw new ValidatorException('Currency is required and must be a string');
        }

        $currency = $data['currency'];

        $this->validateAvailableCurrency($currency);

        return true;
    }

    private function validateAvailableCurrency(string $currency): void
    {
        if (! in_array($currency, $this->currencyRepository->getAvailableCurrencies())) {
            throw new ValidatorException('Currency format is wrong');
        }
    }
}
