<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class TransformerException extends Exception
{
    /**
     * Render the exception into an HTTP response.
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'status' => 400,
            'message' => $this->message,
        ], 400);
    }
}
