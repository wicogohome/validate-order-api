<?php

namespace App\Repositories;

class CurrencyRepository
{
    public function getAvailableCurrencies(): array
    {
        return ['USD', 'TWD'];
    }

    /**
     * 取得貨幣間的匯率mapping
     *
     * 結構如下：
     * - 第一層key是源貨幣代碼（ex. USD）
     * - 第二層key是目標貨幣代碼（ex. TWD）
     * - 第三層包含一個key-value，key為 'rate'，value為匯率
     *
     * @return array<string, array<string, array{rate: float}>>
     *
     * ex.
     * [
     *     'USD' => [
     *         'TWD' => [
     *             'rate' => 31.0
     *         ]
     *     ],
     *     // 可能包含更多對應
     * ]
     */
    public function getCurrencyMapping(): array
    {
        return [
            'USD' => ['TWD' => ['rate' => 31.0]],
        ];
    }

    public function getExchangeRate(string $fromCurrency, string $toCurrency): float
    {
        $mapping = $this->getCurrencyMapping();

        return (float) ($mapping[$fromCurrency][$toCurrency]['rate'] ?? 1);
    }
}
