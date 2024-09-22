<?php

namespace App\Transformers;

use App\Exceptions\TransformerException;
use App\Services\CurrencyService;

class CurrencyTransformer implements TransformerInterface
{
    public function __construct(
        private CurrencyService $currencyService,
    ) {}

    public function transform(array $data): array
    {
        $this->validateInput($data);

        $price = $data['price'];
        $currency = $data['currency'];

        $transformedData = $this->transformByCurrency($price, $currency);

        return array_merge($data, $transformedData);
    }

    private function validateInput(array $data): void
    {
        if (! isset($data['price']) || ! is_numeric($data['price'])) {
            throw new TransformerException('Price is required and must be numeric');
        }

        if (! isset($data['currency']) || ! is_string($data['currency'])) {
            throw new TransformerException('Currency is required and must be a string');
        }
    }

    private function transformByCurrency(float $price, string $currency): array
    {
        $priceInDefaultCurrency = $this->currencyService->convertToDefaultCurrency($price, $currency);
        $targetCurrency = $this->currencyService->getDefaultCurrency();

        return [
            'price' => $priceInDefaultCurrency,
            'currency' => $targetCurrency,
        ];
    }
}
