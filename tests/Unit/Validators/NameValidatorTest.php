<?php

namespace Tests\Unit\Validators;

use PHPUnit\Framework\TestCase;
use App\Validators\NameValidator;
use App\Exceptions\ValidatorException;

class NameValidatorTest extends TestCase
{
    protected $nameValidator;

    protected function setUp(): void
    {
        $this->nameValidator = app(NameValidator::class);
    }

    public function testValidateWithInvalidInput()
    {
        // 錯誤input，並預期 ValidatorException
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Name is required and must be a string');

        $this->nameValidator->validate(['Melody Holiday Inn']);
    }

    public function testValidateWithValidName()
    {
        // 正確
        $this->assertTrue($this->nameValidator->validate(['name' => 'Melody Holiday Inn']));
    }

    public function testValidateWithNonEnglishCharacters()
    {
        // 包含非英文字母，預期拋出 ValidatorException
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Name contains non-English characters');

        $this->nameValidator->validate(['name' => 'Melody Holiday Inn 非英文字元']);
    }

    public function testValidateWithNonCapitalizedName()
    {
        // 每個單字首字母非大寫的名字，預期拋出 ValidatorException
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Name is not capitalized');

        $this->nameValidator->validate(['name' => 'Melody holiday Inn']);
    }

    public function testValidateWithNonEnglishAndNonCapitalized()
    {
        // 含非英文字元 且 未首字母大寫的名字，預期先拋出的 非英文字元的異常
        $this->expectException(ValidatorException::class);
        $this->expectExceptionMessage('Name contains non-English characters');

        $this->nameValidator->validate(['name' => 'Melody holiday Inn 非英文字元']);
    }
}
