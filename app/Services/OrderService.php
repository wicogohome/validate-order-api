<?php

namespace App\Services;

use App\Transformers\CurrencyTransformer;
use App\Validators\CurrencyValidator;
use App\Validators\NameValidator;
use App\Validators\PriceValidator;

class OrderService
{
    private $validators;

    private $transformers;

    public function __construct(
        NameValidator $nameValidator,
        PriceValidator $priceValidator,
        CurrencyValidator $currencyValidator,
        CurrencyTransformer $currencyTransformer
    ) {
        $this->validators = collect([
            $nameValidator,
            $priceValidator,
            $currencyValidator,
        ]);

        $this->transformers = collect([
            $currencyTransformer,
        ]);
    }

    /**
     * 使用$validators and $transformers對 $data 進行驗證與修改。
     *
     *
     * @return array 轉換後的資料
     */
    public function validateAndTransform(array $data): array
    {
        $this->validators->each(fn ($validator) => $validator->validate($data));

        $transformedData = $this->transformers->reduce(
            fn ($currentData, $transformer) => $transformer->transform($currentData),
            $data
        );

        return [
            'id' => $transformedData['id'],
            'name' => $transformedData['name'],
            'address' => [
                'city' => $transformedData['address']['city'],
                'district' => $transformedData['address']['district'],
                'street' => $transformedData['address']['street'],
            ],
            'price' => $transformedData['price'],
            'currency' => $transformedData['currency'],
        ];
    }
}
