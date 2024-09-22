<?php

namespace Tests\Unit\Validators;

use PHPUnit\Framework\TestCase;
use App\Validators\CurrencyValidator;
use App\Exceptions\ValidatorException;
use App\Repositories\CurrencyRepository;

class CurrencyValidatorTest extends TestCase
{
    protected $currencyRepositoryMock;

    protected $currencyValidator;

    protected function setUp(): void
    {
        $this->currencyRepositoryMock = $this->createMock(CurrencyRepository::class);
        $this->currencyValidator = new CurrencyValidator($this->currencyRepositoryMock);
    }

    public function testValidateWithValidCurrencyTWD()
    {
        // TWD
        $this->currencyRepositoryMock
        ->method('getAvailableCurrencies')
        ->willReturn(['USD', 'TWD']);

        $this->assertTrue($this->currencyValidator->validate(['currency' => 'TWD']));
    }

    public function testValidateWithValidCurrencyUSD()
    {
        // USD
        $this->currencyRepositoryMock
        ->method('getAvailableCurrencies')
        ->willReturn(['USD', 'TWD']);
        
        $this->assertTrue($this->currencyValidator->validate(['currency' => 'USD']));
    }


    public function testValidateWithMissingCurrency()
    {
        // 缺少currency，並預期 ValidatorException
        $data = [];

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Currency is required and must be a string');

        $this->currencyValidator->validate($data);
    }

    public function testValidateWithNonStringCurrency()
    {
        // currency型別不對，並預期 ValidatorException
        $data = [
            'currency' => 123,
        ];

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Currency is required and must be a string');

        $this->currencyValidator->validate($data);
    }


    public function testValidateWithInvalidCurrency()
    {
         // 不在範圍內的幣別，並預期 ValidatorException

        $this->currencyRepositoryMock
            ->method('getAvailableCurrencies')
            ->willReturn(['USD', 'TWD']);

        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Currency format is wrong');

        $this->currencyValidator->validate(['currency' => 'ABC']);
    }

    public function testValidateAvailableCurrencyMethodCalled()
    {
        $this->currencyRepositoryMock
            ->method('getAvailableCurrencies')
            ->willReturn(['USD', 'TWD']);

        // 確認validateAvailableCurrency會呼叫getAvailableCurrencies
        $this->currencyRepositoryMock
            ->expects($this->once())
            ->method('getAvailableCurrencies');

        $this->currencyValidator->validate(['currency' => 'TWD']);
    }
}
