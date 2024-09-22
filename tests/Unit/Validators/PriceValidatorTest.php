<?php

namespace Tests\Unit\Validators;

use PHPUnit\Framework\TestCase;
use App\Services\CurrencyService;
use App\Validators\PriceValidator;
use App\Exceptions\ValidatorException;

class PriceValidatorTest extends TestCase
{
    protected $currencyServiceMock;

    protected $priceValidator;

    private float $maxPrice;

    protected function setUp(): void
    {
        $this->currencyServiceMock = $this->createMock(CurrencyService::class);
        $this->priceValidator = app(PriceValidator::class);
        $this->maxPrice = 2000;
    }

    public function testValidateWithMissingPrice()
    {
        // 缺少price，並預期 ValidatorException
        $data = [
            'currency' => 'USD',
        ];

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Price is required and must be numeric');

        $this->priceValidator->validate($data);
    }

    public function testValidateWithNonNumericPrice()
    {
        // price型別不對，並預期 ValidatorException
        $data = [
            'price' => 'abc',
            'currency' => 'USD',
        ];

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Price is required and must be numeric');

        $this->priceValidator->validate($data);
    }

    public function testValidateWithMissingCurrency()
    {
        // 缺少currency，並預期 ValidatorException
        $data = [
            'price' => 100,
        ];

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Currency is required and must be a string');

        $this->priceValidator->validate($data);
    }

    public function testValidateWithNonStringCurrency()
    {
        // currency型別不對，並預期 ValidatorException
        $data = [
            'price' => 100,
            'currency' => 123,
        ];

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Currency is required and must be a string');

        $this->priceValidator->validate($data);
    }

    public function testValidateWithMaxPriceTWD()
    {
        // 有效的價格，小於或等於 $maxPrice
        $this->currencyServiceMock
            ->method('convertToDefaultCurrency')
            ->with(1500, 'TWD')
            ->willReturn((float) 1500);

        $this->assertTrue($this->priceValidator->validate([
            'price' => 1500,
            'currency' => 'TWD',
        ]));
    }

    public function testValidateWithExactMaxPriceTWD()
    {
        // 價格等於 $maxPrice
        $this->currencyServiceMock
            ->method('convertToDefaultCurrency')
            ->with(2000, 'TWD')
            ->willReturn((float) 2000);

        $this->assertTrue($this->priceValidator->validate([
            'price' => 2000,
            'currency' => 'TWD',
        ]));
    }

    public function testValidateWithPriceOverLimitTWD()
    {
        // 價格超過 $maxPrice，並預期 ValidatorException
        $this->currencyServiceMock
            ->method('convertToDefaultCurrency')
            ->with(2500, 'TWD')
            ->willReturn((float) 2500);

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Price is over '.$this->maxPrice);

        $this->priceValidator->validate([
            'price' => 2500,
            'currency' => 'TWD',
        ]);
    }

    public function testValidateWithMaxPriceUSD()
    {
        // 測試有效的價格，小於或等於 $maxPrice
        $this->currencyServiceMock
            ->method('convertToDefaultCurrency')
            ->with(48, 'USD')
            ->willReturn((float) 1500);

        $this->assertTrue($this->priceValidator->validate([
            'price' => 48, // 1500 / 31
            'currency' => 'USD',
        ]));
    }

    public function testValidateWithExactMaxPriceUSD()
    {
        // 測試價格約等於 $maxPrice
        $this->currencyServiceMock
            ->method('convertToDefaultCurrency')
            ->with(64.5, 'USD')
            ->willReturn((float) 2000);

        $this->assertTrue($this->priceValidator->validate([
            'price' => 64.5,
            'currency' => 'USD',
        ]));
    }

    public function testValidateWithPriceOverLimitUSD()
    {
        // 測試價格超過 $maxPrice，並期望拋出 ValidatorException
        $this->currencyServiceMock
            ->method('convertToDefaultCurrency')
            ->with(80, 'USD')
            ->willReturn((float) 2500);

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Price is over '.$this->maxPrice);

        $this->priceValidator->validate([
            'price' => 80, // 2500 / 31
            'currency' => 'USD',
        ]);
    }
}
