<?php

namespace App\Http\Resources\Api;

use App\Dtos\User\UserDto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin User */
class UserResource extends JsonResource
{
    /**
     * @var UserDto
     */
    public $resource;

    /**
     * @param UserDto $resource
     */
    public function __construct(UserDto $resource)
    {
        $this->resource = $resource;
    }

    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     *
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->resource->id,
            'name' => $this->resource->name,
            'email' => $this->resource->email,
            'token' => $this->resource->token,
        ];
    }
}
