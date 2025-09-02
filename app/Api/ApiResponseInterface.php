<?php

namespace App\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

/**
 * API Response Interface
 *
 * This interface defines the contract for formatting consistent API responses
 * throughout the application. It provides methods for both successful and error responses.
 *
 */
interface ApiResponseInterface
{
    /**
     * Send a success response
     *
     * Creates a standardized JSON response for successful API operations.
     *
     * @param string                        $message The success message to include in the response
     * @param int                           $code    The HTTP status code (default: 200)
     * @param array|Collection|JsonResource $data    The data payload to include in the response
     *
     * @return JsonResponse The formatted JSON response
     */
    public function success(string $message, int $code = 200, Collection|JsonResource|array $data = []): JsonResponse;

    /**
     * Send an error response
     *
     * Creates a standardized JSON response for failed API operations.
     *
     * @param string                        $message The error message to include in the response
     * @param int                           $code    The HTTP status code (default: 500)
     * @param array|Collection|JsonResource $errors  Additional error data to include in the response
     *
     * @return JsonResponse The formatted JSON response
     */
    public function error(string $message, int $code = 500, Collection|JsonResource|array $errors = []): JsonResponse;
}
