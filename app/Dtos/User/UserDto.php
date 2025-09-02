<?php

namespace App\Dtos\User;

/**
 * User Data Transfer Object
 *
 * This DTO represents a user entity with its core attributes and authentication token.
 * It is used for transferring user data between layers of the application without
 * exposing the underlying implementation details of the User model.
 *
 */
class UserDto
{
    /**
     * UserDto constructor
     *
     * @param int         $id    The unique identifier of the user
     * @param string      $name  The full name of the user
     * @param string      $email The email address of the user (used for authentication)
     * @param string|null $token The authentication token for API access (optional)
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly string $email,
        public readonly ?string $token = null
    ) {}

    /**
     * Convert the DTO to an array representation
     *
     * @return array<string, mixed> The array representation of the user data
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
        ];

        if ($this->token !== null) {
            $data['token'] = $this->token;
        }

        return $data;
    }
}
