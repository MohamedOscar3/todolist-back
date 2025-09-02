<?php

namespace App\Services;

use App\Api\ApiResponseInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ApiResponseService implements ApiResponseInterface
{
    /**
     * Send success error response
     */
    public function success(string $message, int $code = 200, Collection|JsonResource|array $data = []): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    /**
     * Send Error Json response
     */
    public function error(string $message, int $code = 500, array|Collection|JsonResource $errors = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $code);
    }
}
