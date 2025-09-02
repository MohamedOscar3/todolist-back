<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * User Model
 *
 * This model represents a user in the system with authentication capabilities.
 * It includes API token management, factory support for testing, and notification handling.
 *
 * @property int                             $id                The unique identifier for the user
 * @property string                          $name              The full name of the user
 * @property string                          $email             The email address of the user
 * @property string                          $password          The hashed password of the user
 * @property string|null                     $remember_token    The token used for "remember me" functionality
 * @property \Illuminate\Support\Carbon|null $email_verified_at When the email was verified
 * @property \Illuminate\Support\Carbon      $created_at        When the user was created
 * @property \Illuminate\Support\Carbon      $updated_at        When the user was last updated
 *
 * @method static \Database\Factories\UserFactory            factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 *
 */
class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;

    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
