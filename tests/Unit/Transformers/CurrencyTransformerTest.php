<?php

namespace Tests\Unit\Transformers;

use App\Exceptions\TransformerException;
use App\Services\CurrencyService;
use App\Transformers\CurrencyTransformer;
use PHPUnit\Framework\TestCase;

class CurrencyTransformerTest extends TestCase
{
    protected $currencyServiceMock;

    protected $currencyTransformer;

    protected function setUp(): void
    {
        $this->currencyServiceMock = $this->createMock(CurrencyService::class);
        $this->currencyTransformer = new CurrencyTransformer($this->currencyServiceMock);
    }

    public function testTransformWithValidInput()
    {
        // 正確
        $this->currencyServiceMock
            ->method('convertToDefaultCurrency')
            ->with(100, 'USD')
            ->willReturn((float) 3100);

        $this->currencyServiceMock
            ->method('getDefaultCurrency')
            ->willReturn('TWD');

        $data = [
            'price' => 100,
            'currency' => 'USD',
        ];

        $expectedResult = [
            'price' => 3100,   // 轉換後的價格
            'currency' => 'TWD',  // 目標貨幣
        ];

        $result = $this->currencyTransformer->transform($data);

        $this->assertEquals(array_merge($data, $expectedResult), $result);
    }

    public function testTransformWithMissingPrice()
    {
        $data = [
            // 缺少price
            'currency' => 'USD',
        ];

        $this->expectException(TransformerException::class);
        $this->expectExceptionMessage('Price is required and must be numeric');

        $this->currencyTransformer->transform($data);
    }

    public function testTransformWithNonNumericPrice()
    {
        $data = [
            'price' => 'abc',  // 非數字
            'currency' => 'USD',
        ];

        $this->expectException(TransformerException::class);
        $this->expectExceptionMessage('Price is required and must be numeric');

        $this->currencyTransformer->transform($data);
    }

    public function testTransformWithMissingCurrency()
    {
        $data = [
            // 缺少currency
            'price' => 100,
        ];

        $this->expectException(TransformerException::class);
        $this->expectExceptionMessage('Currency is required and must be a string');

        $this->currencyTransformer->transform($data);
    }

    public function testTransformWithNonStringCurrency()
    {
        $data = [
            'price' => 100,
            'currency' => 123,  // 非字串
        ];

        $this->expectException(TransformerException::class);
        $this->expectExceptionMessage('Currency is required and must be a string');

        $this->currencyTransformer->transform($data);
    }

    public function testTransformWithDefaultCurrency()
    {
        // 預設貨幣，無需轉換
        $this->currencyServiceMock
            ->method('convertToDefaultCurrency')
            ->with(100, 'TWD')
            ->willReturn((float) 100);

        $this->currencyServiceMock
            ->method('getDefaultCurrency')
            ->willReturn('TWD');

        $data = [
            'price' => 100,
            'currency' => 'TWD',
        ];

        $expectedResult = [
            'price' => 100,  // 不需轉換
            'currency' => 'TWD',  // 目標貨幣
        ];

        $result = $this->currencyTransformer->transform($data);

        $this->assertEquals(array_merge($data, $expectedResult), $result);
    }
}
