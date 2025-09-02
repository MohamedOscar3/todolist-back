<?php

namespace App\Dtos\User;

/**
 * Create User Data Transfer Object
 *
 * This DTO contains the necessary data for creating a new user in the system.
 * It encapsulates the user's name, email, and password for transfer between
 * application layers during the user creation process.
 *
 */
class CreateUserDto
{
    /**
     * Constructor for CreateUserDto
     *
     * @param string $name     The full name of the user to be created
     * @param string $email    The email address of the user (must be unique)
     * @param string $password The password for the user account (will be hashed)
     */
    public function __construct(
        public string $name,
        public string $email,
        public string $password,
    ) {}

    /**
     * Convert the DTO to an array representation
     *
     * @return array<string, string> The array representation of the user creation data
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ];
    }
}
