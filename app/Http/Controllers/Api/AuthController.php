<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiResponseInterface;
use App\Dtos\User\CreateUserDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * Authentication Controller
 *
 * Handles user registration and login functionality.
 *
 * @group Authentication
 */
class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @param UserService          $userService Service for user-related operations
     * @param ApiResponseInterface $response    Response formatter
     */
    public function __construct(
        private readonly UserService $userService,
        private readonly ApiResponseInterface $response
    ) {}

    /**
     * Register a new user
     *
     * Creates a new user account with the provided information.
     *
     * @bodyParam name string required The full name of the user. Example: John Doe
     * @bodyParam email string required The email address of the user (must be unique). Example: john@example.com
     * @bodyParam password string required The password for the user account. Example: secret123
     * @bodyParam password_confirmation string required The password confirmation. Example: secret123
     *
     * @responseField success boolean Indicates if the request was successful.
     * @responseField message string A message describing the result of the operation.
     * @responseField data object The user data including authentication token.
     * @responseField data.id integer The unique identifier of the user.
     * @responseField data.name string The name of the user.
     * @responseField data.email string The email address of the user.
     * @responseField data.token string The authentication token for API access.
     *
     * @return JsonResponse
     */
    /**
     * Register a new user
     *
     * Creates a new user account with the provided information.
     *
     * @bodyParam name string required The full name of the user. Example: John Doe
     * @bodyParam email string required The email address of the user (must be unique). Example: john@example.com
     * @bodyParam password string required The password for the user account. Example: secret123
     * @bodyParam password_confirmation string required The password confirmation. Example: secret123
     *
     * @responseField success boolean Indicates if the request was successful.
     * @responseField message string A message describing the result of the operation.
     * @responseField data object The user data including authentication token.
     * @responseField data.id integer The unique identifier of the user.
     * @responseField data.name string The name of the user.
     * @responseField data.email string The email address of the user.
     * @responseField data.token string The authentication token for API access.
     *   }
     * }
     *
     * @response 422 scenario="Validation Error" {
     *   "success": false,
     *   "message": "Validation failed",
     *   "errors": {
     *     "email": ["The email has already been taken."],
     *     "password": ["The password confirmation does not match."]
     *   }
     * }
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $dto = new CreateUserDto(
            name: $request->name,
            email: $request->email,
            password: $request->password,
        );

        $user = $this->userService->create($dto);

        $dto = $this->userService->getUserDto($user);

        return $this->response->success('User created successfully', 201, $dto->toArray());
    }

    /**
     * Login user
     *
     * Authenticates a user and provides an API token.
     *
     * @bodyParam email string required The email address of the user. Example: john@example.com
     * @bodyParam password string required The password for the user account. Example: secret123
     *
     * @responseField success boolean Indicates if the request was successful.
     * @responseField message string A message describing the result of the operation.
     * @responseField data object The user data including authentication token.
     * @responseField data.id integer The unique identifier of the user.
     * @responseField data.name string The name of the user.
     * @responseField data.email string The email address of the user.
     * @responseField data.token string The authentication token for API access.
     *
     * @response scenario="Success" {
     *   "success": true,
     *   "message": "Login successful",
     *   "data": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john@example.com",
     *     "token": "1|abcdefghijklmnopqrstuvwxyz"
     *   }
     * }
     * @response 401 scenario="Invalid Credentials" {
     *   "success": false,
     *   "message": "Invalid credentials",
     *   "errors": {}
     * }
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only(['email', 'password']);

        if (! Auth::attempt($credentials)) {
            return $this->response->error('Invalid credentials', 401);
        }

        $user = User::where('email', $request->email)->first();
        $dto = $this->userService->getUserDto($user);

        return $this->response->success('Login successful', 200, $dto->toArray());
    }
}
