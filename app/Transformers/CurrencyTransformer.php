<?php

namespace App\Transformers;

use App\Exceptions\TransformerException;
use App\Repositories\CurrencyRepository;
use App\Transformers\TransformerInterface;

class CurrencyTransformer implements TransformerInterface
{
    private int $defaultScale;

    private string $targetCurrency;

    public function __construct(
        public CurrencyRepository $currencyRepository,
        int $defaultScale = 2,
        string $targetCurrency = 'TWD',
    ) {
        $this->defaultScale = $defaultScale;
        $this->targetCurrency = $targetCurrency;

        bcscale($this->defaultScale);
    }

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
        $currencyMapping = $this->currencyRepository->getCurrencyMapping();
        $rate = $currencyMapping[$currency][$this->targetCurrency]['rate'] ?? 1;
        $covertedPrice = bcmul($price, $rate);

        return [
            'price' => round((float) $covertedPrice, 0),
            'currency' => $this->targetCurrency,
        ];
    }
}
