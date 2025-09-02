<?php

namespace App\Services;

use App\Dtos\User\CreateUserDto;
use App\Dtos\User\UserDto;
use App\Models\User;

/**
 * User Service Class
 *
 * This class handle everything related to the user
 */
class UserService
{
    public function create(CreateUserDto $dto): User
    {
        return User::create([
            'name' => $dto->name,
            'email' => $dto->email,
            'password' => $dto->password,
        ]);
    }

    /**
     * Generate user access token
     */
    public function generateToken(User $user): string
    {
        return $user->createToken('api-token')->plainTextToken;
    }

    /**
     * Get user dto
     */
    public function getUserDto(User $user): UserDto
    {
        return new UserDto(
            $user->id,
            $user->name,
            $user->email,
            $this->generateToken($user),
        );
    }
}
