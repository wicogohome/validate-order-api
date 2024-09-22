<?php

namespace Tests\Feature;

use Tests\TestCase;

class NormalizeOrderAPITest extends TestCase
{
    public function testSuccessfulResponse(): void
    {
        $validInput = [
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
        $response = $this->postJson(route('api.order.normalize'), $validInput);
        $response->assertStatus(200);
    }

    public function testInvalidInputResponse(): void
    {
        $invalidInput = [];
        $response = $this->postJson(route('api.order.normalize'), $invalidInput);
        $response->assertStatus(422);
    }

    public function testExampleResponse(): void
    {
        $exampleInput = [
            'id' => 'A0000001',
            'name' => 'Melody Holidy Inn',
            'address' => [
                'city' => 'taipei-city',
                'district' => 'da-an-district',
                'street' => 'fuxing-south-road',
            ],
            'price' => 2500,
            'currency' => 'TWD',
        ];

        $response = $this->postJson(route('api.order.normalize'), $exampleInput);
        $response->assertStatus(400);
    }
}
