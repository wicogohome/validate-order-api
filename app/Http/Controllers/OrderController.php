<?php

namespace App\Http\Controllers;

use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use App\Http\Requests\NormalizeOrderRequest;

class OrderController extends Controller
{
    public function __construct(public OrderService $OrderService) {}

    /**
     * 透過驗證與轉換，將Order data統一為指定格式
     *
     * @param NormalizeOrderRequest
     *
     * @throws \Throwable
     */
    public function normalize(NormalizeOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $transformedResult = $this->OrderService->validateAndTransform($data);
        } catch (\Throwable $e) {
            throw $e;
        }

        return response()->json([
            'status' => 200,
            'data' => $transformedResult,
        ]);
    }
}
