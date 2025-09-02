<?php

namespace App\Http\Controllers\Api;

use App\Api\ApiResponseInterface;
use App\Dtos\User\CreateUserDto;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Auth\RegisterRequest;
use App\Http\Resources\Api\UserResource;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class AuthController extends Controller
{
    public function __construct(
        private readonly UserService $userService,
        private readonly ApiResponseInterface $response
    ) {}

    /**
     * Register new User
     *
     * @param RegisterRequest $request
     *
     * @return JsonResponse
     */
    public function register(RegisterRequest $request)
    {
        $dto = new CreateUserDto(
            name: $request->name,
            email: $request->email,
            password: $request->password,
        );

        $user = $this->userService->create($dto);

        $dto = $this->userService->getUserDto($user);

        return $this->response->success('User created successfully', 201, new UserResource($dto));
    }
}
