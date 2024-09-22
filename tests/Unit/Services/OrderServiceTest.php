<?php

namespace Tests\Unit\Services;

use App\Services\OrderService;
use PHPUnit\Framework\TestCase;
use App\Validators\NameValidator;
use App\Validators\PriceValidator;
use App\Validators\CurrencyValidator;
use App\Exceptions\ValidatorException;
use App\Transformers\CurrencyTransformer;

class OrderServiceTest extends TestCase
{
    protected $nameValidatorMock;
    protected $priceValidatorMock;
    protected $currencyValidatorMock;
    protected $currencyTransformerMock;
    protected $orderService;

    protected function setUp(): void
    {
        $this->nameValidatorMock = $this->createMock(NameValidator::class);
        $this->priceValidatorMock = $this->createMock(PriceValidator::class);
        $this->currencyValidatorMock = $this->createMock(CurrencyValidator::class);
        $this->currencyTransformerMock = $this->createMock(CurrencyTransformer::class);

        $this->orderService = new OrderService(
            $this->nameValidatorMock,
            $this->priceValidatorMock,
            $this->currencyValidatorMock,
            $this->currencyTransformerMock
        );
    }

    public function testValidateAndTransformWithValidData()
    {
        $data = [
            'id' => 'A0000001',
            'name' => 'Melody Holidy Inn',
            'address' => [
                'city' => 'taipei-city',
                'district' => 'da-an-district',
                'street' => 'fuxing-south-road',
            ],
            'price' => 2000,
            'currency' => 'TWD',
        ];

        // Validator都正確通過驗證
        $this->nameValidatorMock
            ->method('validate')
            ->with($data)
            ->willReturn(true);
        $this->priceValidatorMock
            ->method('validate')
            ->with($data)
            ->willReturn(true);
        $this->currencyValidatorMock
            ->method('validate')
            ->with($data)
            ->willReturn(true);

        // Transformer都正確轉換資料（為目標貨幣，不須變動）
        $transformedData = array_merge($data, [
            'price' => 2000,
            'currency' => 'TWD',
        ]);

        $this->currencyTransformerMock
            ->method('transform')
            ->with($data)
            ->willReturn($transformedData);


        $result = $this->orderService->validateAndTransform($data);

        // 結果應相同
        $this->assertEquals([
            'id' => 'A0000001',
            'name' => 'Melody Holidy Inn',
            'address' => [
                'city' => 'taipei-city',
                'district' => 'da-an-district',
                'street' => 'fuxing-south-road',
            ],
            'price' => 2000,
            'currency' => 'TWD',
        ], $result);
    }

    public function testValidateAndTransformThrowsExceptionForInvalidData()
    {
        $data = [
            'id' => 'A0000001',
            // 缺少name
            'address' => [
                'city' => 'taipei-city',
                'district' => 'da-an-district',
                'street' => 'fuxing-south-road',
            ],
            'price' => 2000,
            'currency' => 'TWD',
        ];

        // 假設有Validator拋出 ValidatorException
        $this->nameValidatorMock
            ->method('validate')
            ->with($data)
            ->willThrowException(new ValidatorException('Invalid'));

        // 預期拋出異常
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Invalid');

        $this->orderService->validateAndTransform($data);
    }

    public function testValidateAndTransformAppliesTransformersCorrectly()
    {
        $data = [
            'id' => 'A0000001',
            'name' => 'Melody Holidy Inn',
            'address' => [
                'city' => 'taipei-city',
                'district' => 'da-an-district',
                'street' => 'fuxing-south-road',
            ],
            'price' => 20,
            'currency' => 'USD',
        ];

        // Validator都正確通過驗證
        $this->nameValidatorMock
            ->method('validate')
            ->with($data)
            ->willReturn(true);
        $this->priceValidatorMock
            ->method('validate')
            ->with($data)
            ->willReturn(true);
        $this->currencyValidatorMock
            ->method('validate')
            ->with($data)
            ->willReturn(true);

        // Transformer都正確轉換資料
        $transformedData = array_merge($data, [
            'price' => 620, // 20 * 31
            'currency' => 'TWD',
        ]);

        $this->currencyTransformerMock
            ->method('transform')
            ->with($data)
            ->willReturn($transformedData);


        $result = $this->orderService->validateAndTransform($data);

        // 結果應被正確轉換
        $this->assertEquals([
            'id' => 'A0000001',
            'name' => 'Melody Holidy Inn',
            'address' => [
                'city' => 'taipei-city',
                'district' => 'da-an-district',
                'street' => 'fuxing-south-road',
            ],
            'price' => 620,
            'currency' => 'TWD',
        ], $result);
    }
}
