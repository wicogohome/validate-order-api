<?php

namespace App\Validators;

use App\Exceptions\ValidatorException;

class PriceValidator implements ValidatorInterface
{
    private int $validPrice;

    public function __construct(int $validPrice = 2000)
    {
        $this->validPrice = $validPrice;
    }

    public function validate(array $data): bool
    {
        if (! isset($data['price']) || ! is_numeric($data['price'])) {
            throw new ValidatorException('Price is required and must be numeric');
        }

        $price = $data['price'];
        $this->validatePriceNotOver($price);

        return true;
    }

    private function validatePriceNotOver($price): void
    {
        if ($price > $this->validPrice) {
            throw new ValidatorException('Price is over '.$this->validPrice);
        }
    }
}
