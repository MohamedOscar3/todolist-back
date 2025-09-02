<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiResponseInterface;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * User Controller
 *
 * Handles user profile operations.
 *
 * @group User Profile
 *
 * @authenticated
 */
class UserController extends Controller
{
    /**
     * Create a new UserController instance.
     *
     * @param UserService          $userService Service for user-related operations
     * @param ApiResponseInterface $response    Response formatter
     */
    public function __construct(
        private readonly UserService $userService,
        private readonly ApiResponseInterface $response
    ) {}

    /**
     * Get authenticated user profile
     *
     * Returns the profile information for the currently authenticated user.
     *
     * @param Request $request The incoming request
     *
     * @return JsonResponse
     *
     * @responseField success boolean Indicates if the request was successful.
     * @responseField message string A message describing the result of the operation.
     * @responseField data object The user data.
     * @responseField data.id integer The unique identifier of the user.
     * @responseField data.name string The name of the user.
     * @responseField data.email string The email address of the user.
     *
     * @response {
     *   "success": true,
     *   "message": "User profile retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com"
     *   }
     * }
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $userDto = $this->userService->getUserDtoWithoutToken($user);

        return $this->response->success(
            'User profile retrieved successfully',
            200,
            $userDto->toArray()
        );
    }
}
