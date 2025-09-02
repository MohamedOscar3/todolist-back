<?php

namespace App\Services;

use App\Dtos\User\CreateUserDto;
use App\Dtos\User\UserDto;
use App\Models\User;

/**
 * User Service Class
 *
 * This class handles everything related to user management including creation,
 * authentication token generation, and data transfer object conversion.
 *
 */
class UserService
{
    /**
     * Create a new user
     *
     * @param CreateUserDto $dto Data transfer object containing user creation data
     *
     * @return User The newly created user model with generated ID
     */
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
     *
     * @param User $user User model with valid ID
     *
     * @return string The generated API access token
     */
    public function generateToken(User $user): string
    {
        return $user->createToken('api-token')->plainTextToken;
    }

    /**
     * Get user data transfer object with token
     *
     * Converts a User model to a UserDto including the user's ID and a newly generated token
     *
     * @param User $user User model with valid ID
     *
     * @return UserDto Data transfer object containing user ID, name, email and token
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

    /**
     * Get user data transfer object without token
     *
     * Converts a User model to a UserDto including only the user's basic information
     * without generating a new authentication token
     *
     * @param User $user User model with valid ID
     *
     * @return UserDto Data transfer object containing user ID, name, and email
     */
    public function getUserDtoWithoutToken(User $user): UserDto
    {
        return new UserDto(
            $user->id,
            $user->name,
            $user->email
        );
    }
}
