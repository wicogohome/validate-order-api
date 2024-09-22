<?php

namespace Tests\Unit\Services;

use App\Repositories\CurrencyRepository;
use App\Services\CurrencyService;
use PHPUnit\Framework\TestCase;

class CurrencyServiceTest extends TestCase
{
    protected $currencyRepositoryMock;

    protected $currencyService;

    private string $defaultCurrency;

    protected function setUp(): void
    {
        $this->currencyRepositoryMock = $this->createMock(CurrencyRepository::class);
        $this->currencyService = new CurrencyService($this->currencyRepositoryMock);

        $this->defaultCurrency = 'TWD';

        bcscale(2);
    }

    public function testConvertToDefaultCurrencyWithDifferentCurrency()
    {
        // 測試不同貨幣轉換

        $price = 100;
        $fromCurrency = 'USD';
        $defaultCurrency = 'TWD';
        $exchangeRate = (float) 31;

        $this->currencyRepositoryMock
            ->method('getExchangeRate')
            ->with($fromCurrency, $defaultCurrency)
            ->willReturn($exchangeRate);

        $result = $this->currencyService->convertToDefaultCurrency($price, $fromCurrency);

        $expectedPrice = round(bcmul($price, $exchangeRate), 0);

        $this->assertEquals($expectedPrice, $result);
    }

    public function testConvertToDefaultCurrencyWithSameCurrency()
    {
        // 測試相同貨幣不進行轉換

        $price = 100;
        $fromCurrency = $this->defaultCurrency;

        $result = $this->currencyService->convertToDefaultCurrency($price, $fromCurrency);

        $this->assertEquals($price, $result);
    }

    public function testGetDefaultCurrency()
    {
        $result = $this->currencyService->getDefaultCurrency();
        $this->assertEquals($this->defaultCurrency, $result);
    }
}
